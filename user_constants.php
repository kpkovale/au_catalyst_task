<?php

const HELP_MESSAGE = <<<EOD
user_upload.php [-u=<...> & -p=<...> & -h=<...> [& -n=<...>]]
                [--create_table] [--file=<...>] [--dry_run] [--help]
The following set of commands is specified for current PHP script.

To define commands value both space or equality delimiters are fine.
Example: < -u username > | < -u=username >
         < --file=filename > | < --file filename > | < --file [filename] >

Commands definitions:
• --help – outputs the list of directives with details.
• --file [csv file name] – this is the name of the CSV to be parsed.
• --create_table – this will cause the MySQL table to be built
  (and no further action will be taken in accordance with the task's conditions)
• --dry_run – this should be used together with the --file directive.
  The script will be executed, but the database won't be altered
  --- ---
  The set of options listed below can be ommited.
  However, user input will be requested during the script execution.
• -u [username] – MySQL username
• -p [password] – MySQL password
• -h [host:port] – MySQL host
• -n [DBName] – MySQL database name

EOD;

const NO_OPTIONS_MESSAGE = <<<EOD
Script options are not specified.
Please use --help to see the set of commands available.
EOD;

const DRY_RUN_MESSAGE = "ATTENTION! Script is running in a DRY_RUN mode." . PHP_EOL;
const NEED_FILE_MESSAGE = "Script defined exception: File path or file name is required. Please restart the script using correct parameters".PHP_EOL;
const USE_HELP_MESSAGE = "Use --help to check the list of available commands.".PHP_EOL;
const DRY_RUN_EXIT_MESSAGE = <<<EOD
The script has been executed successfully.
The amount of valid user records prepared to be inserted into the database is: 
EOD;
?>
