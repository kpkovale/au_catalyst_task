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
    }
      if ($i == count($options) && $end_cicle == 0) {
      $err_msg =
"The argument \"".$given_arg."\" does not correspond to any available command.
Use --help to check the list of available commands.".PHP_EOL;
      return [False, $err_msg];
      }
    }
  }
  return [True, '']; // return True if no mistakes found
}

?>
