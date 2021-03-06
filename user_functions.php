<?php

require_once('user_constants.php');

function check_options($shortopts, $longopts) {
  /* function checks user's mistakes in commands syntax*/
  $argv = $_SERVER['argv'];
  $argc = $_SERVER['argc'];
  $args_list = $argv; //recieving overall list of arguments
  unset($args_list[0]); //remove file name from the arguments list
  $optind = null;

  $options = getopt($shortopts, $longopts, $optind);
  /**/
  if ($optind !== $argc) {
    $err_msg =
"An error ocured on \"".($optind+1)."\" argument out of \"$argc\".
The argument \"".$argv[$optind]."\" does not correspond to any available command.
Use --help to check the list of available commands.".PHP_EOL;

    return [False, $err_msg];
  }
  elseif ((count($options) === 0)&&($optind === $argc)&&($argc !== 1)) {
    $err_msg =
"An error ocured on \"".($optind)."\" argument out of \"$argc\".
The argument \"".$argv[$optind-1]."\" does not correspond to any available command.
Use --help to check the list of available commands.".PHP_EOL;
    return [False, $err_msg];
  }
  elseif ($argc === 1) {
    $err_msg = NO_OPTIONS_MESSAGE;
    return [False, $err_msg];
  }

  foreach ($args_list as $given_arg) {
    $end_cicle = 0;
    for ($i=1; $i <= count($options) ; $i++) {
      if ($end_cicle === 1) break;
      foreach ($options as $option_key => $option_value) {
        if ($given_arg === "-".$option_key || $given_arg === "--".$option_key) {
          $end_cicle = 1;
          break;
        }
        elseif ($given_arg === $option_value) {
          $end_cicle = 1;
          break;
        }
        elseif ($given_arg === "-".$option_key."=".$option_value) {
          $end_cicle = 1;
          break;
        }
        elseif ($given_arg === "--".$option_key."=".$option_value) {
          $end_cicle = 1;
          break;
        }
    }
      if ($i == count($options) && $end_cicle == 0) {
        if (strlen($given_arg) === 2 && strpos($given_arg,"-") !== false) {
          $err_msg = "The argument \"".$given_arg."\" requires a vallue to be assigned."
                     . PHP_EOL. USE_HELP_MESSAGE;
          return [False, $err_msg];
        }
        else {
          $err_msg = "The argument \"".$given_arg."\" does not correspond to any available command."
                     . PHP_EOL. USE_HELP_MESSAGE;
          return [False, $err_msg];
        }

      }
    }
  }
  return [True, '']; // return True if no mistakes found
}

function get_dbconnection_params($optionsArray, $paramKeys){

  /* funcion returns array of values from array by given keys */

  $dbConnectionParamsArray = array();
  foreach ($paramKeys as $keyName) {
    if (isset($optionsArray[$keyName])) {
      $dbConnectionParamsArray[] = $optionsArray[$keyName];
    }
    else {
      $dbConnectionParamsArray[] = '' ;
    }
  }
  return $dbConnectionParamsArray;
}

function check_connection_params_value($paramName='',$paramValue) {

  /* checks for mandatory parameters' values and requests input for empy ones */

  $defaultValue = '';
  $functionToCall = 'readline';
  switch (strtolower($paramName)) {
    case 'username':
      $defaultValue = 'root';
      $functionToCall = 'readline';
      break;
    case 'password':
      $defaultValue = ' ';
      $functionToCall = 'request_password_obscured';
      break;
    case 'host':
      $defaultValue = '127.0.0.1:3306';
      $functionToCall = 'readline';
      break;
    case 'db name':
      $defaultValue = 'test';
      $functionToCall = 'readline';
      break;
    default:
      exit("Unable to recognize parameter name $paramName \n");
      break;
}
    while (!$paramValue) {
      echo "Parameter \"$paramName\" is not set while required".PHP_EOL;
      $userPick = readline("\"Y/y\" to set new value OR \"N/n\" for default ($defaultValue): ");
      if (strtoupper($userPick) === "Y") {
        $paramValue = call_user_func($functionToCall, "Enter $paramName: ");
      }
      elseif (strtoupper($userPick) === "N") {
        $paramValue = $defaultValue;
      }
  }
      return $paramValue;
}

function get_host_port_split($hostString) {
  if (strpos($hostString, ':') !== false) {
    $dbHostPort = explode(':', $hostString);
    $dbHost = $dbHostPort[ 0 ];
    $dbPort = $dbHostPort[ 1 ];
    return array($dbHost,$dbPort);
  } else return array($hostString,NULL);
}

function request_password_obscured($prompt='') {

    // function hides output
    // and returns string entered in terminal

    readline_callback_handler_install('', function(){});
    if (isset($prompt)) {
      echo $prompt."\n";
    }
    //echo("Password: ");
    $strHidden = '';
    while (True) {
      $strChar = stream_get_contents(STDIN, 1);
      if ($strChar === chr(10)) {
        break;
      }
      elseif (($strChar===chr(127)) && (strlen($strHidden)!=0)) {
        $strHidden = rtrim($strHidden, $strHidden[strlen($strHidden)-1]);
      }
      else {
        $strHidden .= $strChar;
      }
    }
    readline_callback_handler_remove();
    return $strHidden;
}

function check_table_in_db($dbConnection, $dbName) {
  //Returns table_name if the table exists of null otherwise

  $queryFindTable = "SELECT table_name FROM information_schema.tables
                     WHERE table_schema='$dbName' AND table_name='users';";
  $selectResult = $dbConnection->query($queryFindTable);
  $selectResult->data_seek($selectResult->num_rows - 1);
  $rowSelect = $selectResult->fetch_array()[0];
  return $rowSelect;
}

function check_email_exist($dbConnection, $dbName, $email) {
  // Returns EMAIL if exists or NULL otherwise

  $queryFindEmail = "SELECT email FROM $dbName.users
                     WHERE email=\"$email\"";
  $selectResult = $dbConnection->query($queryFindEmail);
  $selectResult->data_seek($selectResult->num_rows - 1);
  $rowSelect = $selectResult->fetch_array()[0];
  return $rowSelect;
}

?>
