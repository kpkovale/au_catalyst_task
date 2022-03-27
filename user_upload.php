<?php

// connecting files with user defined constants and functions
require_once 'user_constants.php';
require_once 'user_functions.php';

// setting reporting level to ERROR to avoid unnecessary WARNINGS
error_reporting(E_ERROR);

$shortopts = "u:p::h:n:";
$longopts = ["file:","create_table","dry_run","help"];
$options = getopt($shortopts, $longopts);

// Checking script running options
[$checkResult, $errMessage] = check_options($shortopts, $longopts);
if (!$checkResult) {
  echo $errMessage.PHP_EOL;
  exit();
}

// Showing --help message
if (isset($options["help"])) exit(HELP_MESSAGE.PHP_EOL);

/* --- CHECKING FOR THE DRY RUN OF THE SCRIPT --- */
if (isset($options["dry_run"])) {
    $isDryRun = true;
    echo DRY_RUN_MESSAGE;
} else $isDryRun = false;

/* --- *.csv file processing block --- */
If (!isset($options[ "create_table" ])) { //skip file processing if --create_table given
  if (!isset($options["file"]) || empty($options["file"])) {
      exit(NEED_FILE_MESSAGE);
  }
  else {
      echo "---File processing begin:".PHP_EOL; // script info message
      $filePath = $options["file"]; // if --file parameter is not empty
      if (strpos($filePath,'[') === 0 && strpos($filePath,']') === strlen($filePath) - 1) {
          $filePath = substr($file_path,1,strlen($filePath)-2);
      }
  }

  /*   --- if file parameter is handled on run --> reading file and validating data ---   */
  if ($filePath != null) {
      /*   --- inserting file data into array ---   */
      $fileLinesArray = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

      if ($fileLinesArray === false) {
        exit("file \"$filePath\" is empty or not exists. No records were found. Please try another file.\n");
      }

      // checking for the header in file
      // if the 1st line has the ",surname," in it
      if (str_replace(" ", "", strpos($fileLinesArray[0],',surname,')) !== false) {
        array_splice($fileLinesArray, 0, 1); //remove the line
      }

      //creating an array with the insert data
      for ($i = 0; $i < count($fileLinesArray); $i++) {
          $file[$i] = explode(",", $fileLinesArray[$i]);
      }

      /*  --- Setting name and surnames to start from Capital letter and lowering other letters ---   */
      $regexpPattern = '/[A-z]+(\'|-)[A-z]+|[A-z]+/';
      for ($i = 0; $i < count($file); $i++) {
          for ($j = 0; $j < 2; $j++) {

            // checking whether the name or surname is simple (like Sam Jones)
            // or it is a complex one (Mc'Donalds, Smith-Jones) to capitalize 2nd part also
              $delimiterPosition = strpos($file[$i][$j],"'");
              if ($delimiterPosition === false) {
                $delimiterPosition = strpos($file[$i][$j],"-");
              }

              // checking for the apostrophe or the dash in the name/surname
              if ($delimiterPosition === false) {
                $file[$i][$j] = trim(strtoupper(substr($file[$i][$j], 0, 1))
                                .strtolower(substr($file[$i][$j], 1))
                                );
              }
              else {
                $file[$i][$j] = trim(strtoupper(substr($file[$i][$j], 0, 1))
                                .strtolower(substr($file[$i][$j], 1, $delimiterPosition))
                                .strtoupper(substr($file[$i][$j], $delimiterPosition+1, 1))
                                .strtolower(substr($file[$i][$j], $delimiterPosition+2))
                                );
              }
              //Removing extra characters from Names and Surnames
              // except for letters and dash or apostrophe in the middle
              $file[$i][$j] = str_replace(preg_filter($regexpPattern,'',$file[$i][$j]),'',$file[$i][$j]);
          }
      }

      /*  --- lowering and validating e-mails --- */
      $emailCount = 0;
      for ($i = 0; $i < count($file); $i++) {
          $file[$i][2] = trim(strtolower($file[$i][2])); // also trimming the spaces
          if (filter_var($file[$i][2], FILTER_VALIDATE_EMAIL) != false) { //e-mail is valid
              $file[$i][3] = true;
              $emailCount++;
          }
          else {  // e-mail is invalid
              $file[$i][3] = false;
              $email = $file[$i][2];
              echo "email: $email is invalid" . PHP_EOL;
          }
      }
      echo "Valid emails: $emailCount | Invalid emails:". (count($file)-$emailCount) . PHP_EOL;

  }
  echo "---File processing end.",PHP_EOL;
}

/* --- CHECKING FOR DRY_RUN MODE --- */
/* -- No DataBase alteration with this option should be performed -- */
  ($isDryRun) ? exit(DRY_RUN_EXIT_MESSAGE.$emailCount.PHP_EOL) : NULL;

// Collecting database connection parameters
[$dbUser, $dbPassword, $dbHost, $dbName] = get_dbconnection_params($options, ['u','p','h','n']);

$dbUser = check_connection_params_value('Username', $dbUser);
$dbPassword = check_connection_params_value('Password', $dbPassword);
$dbHost = check_connection_params_value('Host', $dbHost);
$dbName = check_connection_params_value('DB Name', $dbName);

/* Checking "host" format as "host:port" or "host" only and assigning values*/
[$dbHost, $dbPort] = get_host_port_split($dbHost);

// echo "username: " . $dbUser . ", "
//     ."password: " . $dbPassword . ", "
//     ."host: " . $dbHost . ", "
//     ."port: " . $dbPort . ", "
//     ."database: " . $dbName . PHP_EOL;

$dbConnection = mysqli_connect($dbHost,$dbUser,$dbPassword,$dbName,$dbPort)
or die("ATTENTION: The database has returned error:".mysqli_connect_error().PHP_EOL
      ."Unable to establish database connection".PHP_EOL);

echo ($dbConnection->select_db($dbName)) ? "DB connection established successfully: "
    .$dbName.PHP_EOL : "The database is not specified!".PHP_EOL;

// Checking whether the 'users' table on specified schema exists
$usersTableExists = is_null(check_table_in_db($dbConnection, $dbName));

if (isset($options[ "create_table" ])) {

  if ($usersTableExists) { // if users table does not exist in selected schema (database)
    /* Forming a "create table" query */
    $queryCreateTable = "CREATE TABLE IF NOT EXISTS $dbName.users (
    id serial PRIMARY KEY NOT NULL,
    u_name VARCHAR(50) NOT NULL,
    u_surname VARCHAR(50) NOT NULL,
    email VARCHAR(350) UNIQUE NOT NULL);";

    $dbConnection->select_db($dbName);
    if ($dbConnection->query($queryCreateTable) === true) {
      echo "Table 'users' on schema '$dbName' created successfully".PHP_EOL;
    }
    else {
      echo "Error creating table: " . $dbConnection->error.PHP_EOL;
    }
    mysqli_close($dbConnection); //close the connection
    exit(); //finish script execution
  }
  else { // if the table exists
    mysqli_close($dbConnection); //close the connection
    exit("Table 'users' already exists on '$dbName' schema".PHP_EOL); //finish script execution
  }
}

// $dbConnection->begin_transaction();
// $dbConnection->commit();
// $dbConnection->rollback();

//mysqli_query(mysqli $mysql, string $query, int $result_mode = MYSQLI_STORE_RESULT): mysqli_result|bool

mysqli_close($dbConnection); // Make sure we closed the connection to the database
?>
