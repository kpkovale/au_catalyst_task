# au_catalyst_task
## Task 1 (Script task)
# INFORMATION AND ASSUMPTIONS.

The following information is related to user_upload.php script (Script further in the text) and assumptions which are necessary for its performance.

## Information and requirements:
1. Operating System. The Script execution has been checked on both OS Windows 10 and Ubuntu 18.04/20.04.
2. PHP version. For successul exectution, PHP version 7.4 or above is required. (the same as in the task)
3. PHP Libraries. MySQL compatibility package for PHP is recommended:
  `sudo apt-get install php7.4-mysql`
4. Database. The MYSQL database 8.0.28 version had been used during testing. Therefore v8 or higher is recommended to be installed.
5. Database. According to task conditions, only "create table", "insert" and "select" operations are specified.
   Therefore at least one database on the host machine is reqiured.   
   - For successul performance, a valid database or schema should be given with "-n" option.
6. Web-server. For successful PHP and Database interaction apache2 or nginx web-server is required.

### To install necessary components the below list of commands is recommended:
- `sudo apt install php` - Enables php usage on current machine. You can use `sudo apt show php` to check the version link in your repository.
For the latest version installation use `sudo apt update` or specify php version with `sudo apt install php7.4` command.
- `sudo apt-get install php7.4-mysql` - Enables MySQL compatibility package for PHP
- Apache2 web-server `sudo apt-get install apache2` or Nginx web-server `sudo apt install nginx`
- `sudo apt install mysql-server` - Allows to install MySQL server on you local machine

## Assumptions for Command Line Directives:

*   --file [file name]
Current directive is required for every Script run. Exception: running with --help or --create_table directives.
File name can be written both as in square brackets (example: `--file [users.csv]`), as without them (example: `--file users.csv`). Equality symbol "=" is also acceptable.   
Example: `--file=filename` | `--file filename` | `--file [filename]`
In a CSV file either ',' or ';' symbol should be used as a delimeter between fields. (One for the whole file)

*   --create_table
To create "users" table in the database, the database or schema name with the `-n` directive should be given.
If the --create_table directive was given, the Script will terminate after successul creation or responce from the database that table with such name already exists, according to task conditions.

*   --dry_run
The dry run mode requires at least --file directive to be specified. Other directives are not necessary with --dry_run.

*   --help
The directive provides infromation for all acceptable commands for user_upload.php Script

Following database user details (directives) are configurable:
*   -u
MySQL username. Required for all Script executions except --dry_run and --help.
Input example: `-u username` | `-u=username`   
*   -p
MySQL password. Required for all Script executions except --dry_run and --help.
Input example: `-p` | `-p='password'`   
In first condition, further password input will be requested during Script execution.
Second case is possible, but recommended for localhost in a closed circuit network only, as BASH stores the commands in memory which creates vulnerabilities.   
*   -h
MySQL host. Required for all Script executions except --dry_run and --help.
Can be used in a format: "host:port" (example: -h localhost:3306) or host only (example: localhost).
If the port is not specified, default MySQL port as "null" will be set.
Input example: `-h host` | `-h=host`
*   -n
MySQL database name. If not specified, "test" database name will be used.
Input example: `-n dbname` | `-n=dbname`

Regardless of whether some DB-connection parameters (-u | -p | -h | -n) are specified or missing, the Script performs their check (if the connection to the DataBase required by conditions: not `--dry_run` or `--help`).
In case there are missing parameters, the Script will request their input or offer "default values".

## Task 2 (Logic test)
The Script requires at least PHP v5 or higher to be installed and no further recommendations will be suggested;
