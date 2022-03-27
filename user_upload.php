<?php

// connecting files with user defined constants and functions
require_once 'user_constants.php';
require_once 'user_functions.php';

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
If (!isset($options[ "create_table" ])) {
  if (!isset($options["file"]) || empty($options["file"])) {
      exit(NEED_FILE_MESSAGE);
  }
  else {
      $filePath = $options["file"]; // if --file parameter is not empty
      if (strpos($filePath,'[') === 0 && strpos($filePath,']') === strlen($filePath) - 1) {
          $filePath = substr($file_path,1,strlen($filePath)-2);
      }
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

            // checking for the apostrophe or the dash in the name
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

?>
