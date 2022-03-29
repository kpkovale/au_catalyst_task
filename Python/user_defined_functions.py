import argparse
import csv


# Forms the available set of CLI commands and reads them from command line
def get_CLI_options(optionsArray):
    parser = argparse.ArgumentParser(conflict_handler='resolve')
    longoptGroup = parser.add_argument_group('List of general comand definitions')
    longoptGroup.add_argument('--file', nargs="?",
                              help="Handles the CSV-file to be parsed.",
                              default="users.csv")
    longoptGroup.add_argument('--create_table',  action='count',
                              help="This will cause the MySQL table to be built")
    longoptGroup.add_argument('--dry_run', action='count',
                              help="The script will be executed, but the database "
                                   + "won't be altered")
    shortoptGroup = parser.add_argument_group('DB connection parameters',
                        "BASH input for the following commands can be omitted")
    shortoptGroup.add_argument('-u', nargs="?", help="[username] – MySQL username")
    shortoptGroup.add_argument('-p', nargs="?", help="[password] – MySQL password")
    shortoptGroup.add_argument('-h', nargs="?", help="[host:port] – MySQL host")
    shortoptGroup.add_argument('-n', nargs="?", help="[DBName] – MySQL database name")
    return parser.parse_args(optionsArray)


# Capitalizes simle and two-part names and surnames
def format_complex_name(stringVal):
    if stringVal.find("'") != -1:
        delimterPos = stringVal.find("'")
        resultString = "{}{}".format(stringVal[0:delimterPos+1].capitalize(),
                                     stringVal[delimterPos+1:].capitalize())
        return resultString
    elif stringVal.find("-") != -1:
        delimterPos = stringVal.find("-")
        resultString = "{}{}".format(stringVal[0:delimterPos+1].capitalize(),
                                     stringVal[delimterPos+1:].capitalize())
        return resultString
    else:
        return stringVal.capitalize()


# define csv delimiter
def get_csv_delimiter(file):
    reader = csv.reader(file)
    if (next(reader)[0].find(";") != -1):
        file.seek(0)
        return ";"
    else:
        file.seek(0)
        return ","
