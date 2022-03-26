<?php

// connecting files with user defined constants and functions
require_once 'user_constants.php';
require_once 'user_functions.php';

$shortopts = "u:p::h:n:";
$longopts = ["file:","create_table","dry_run","help"];

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


?>
