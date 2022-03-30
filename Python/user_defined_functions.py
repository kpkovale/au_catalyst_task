import argparse
import csv
from getpass import getpass


# Forms the available set of CLI commands and reads them from command line
def get_CLI_options(optionsArray):
    parser = argparse.ArgumentParser(conflict_handler='resolve')
    longoptGroup = parser.add_argument_group('List of general comand definitions')
    longoptGroup.add_argument('--file', nargs="?",
                              help="Handles the CSV-file to be parsed.",
                              default="../users.csv")
    longoptGroup.add_argument('--create_table',  action='count',
                              help="This will cause the MySQL table to be built")
    longoptGroup.add_argument('--dry_run', action='count',
                              help="The script will be executed, but the database "
                                   + "won't be altered")
    shortoptGroup = parser.add_argument_group('DB connection parameters',
                        "BASH input for the following commands can be omitted")
    shortoptGroup.add_argument('-u', nargs="?",
                               help="[username] – MySQL username")
    shortoptGroup.add_argument('-p', nargs="?",
                               help="[password] – MySQL password")
    shortoptGroup.add_argument('-h', nargs="?",
                               help="[host:port] – MySQL host")
    shortoptGroup.add_argument('-n', nargs="?",
                               help="[DBName] – MySQL database name")
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


# checks DB connection parameters and requests user input if
# parameter value is not set
def get_dbconnection_params(parser, argNames):
    inputMessage = "Value '{}' is not set. Enter"
    resParamsDict = dict()
    for argument in argNames:
        if ((parser.__getattribute__(argument) is None) & (argument != "p")):
            resParamsDict[decode_shortopts_names(argument)] = input(
                    inputMessage.format(
                        decode_shortopts_names(argument))+": ")
        elif ((parser.__getattribute__(argument) is None) & (argument == "p")):
            resParamsDict[decode_shortopts_names(argument)] = getpass(
                    inputMessage.format(
                        decode_shortopts_names(argument))+": ")
        else:
            resParamsDict[decode_shortopts_names(
                    argument)] = parser.__getattribute__(argument)
    return resParamsDict


# decode shortopts names
def decode_shortopts_names(option):
    if option == "u":
        return "Username"
    if option == "p":
        return "Password"
    if option == "h":
        return "Host"
    else:
        return "Database"


# split "host:port" format into separate host & port values
def get_host_port_split(hostPort):
    if (hostPort.find(":") != -1):
        return ({"Host": hostPort.split(":")[0],
                "Port": hostPort.split(":")[1]})
    else:
        return ({"Host": hostPort,
                "Port": ""})


# Returns table_name if the table exists or None otherwise
def check_table_in_db(connection, dbName):
    queryFindTable = """SELECT table_name FROM information_schema.tables
                     WHERE table_schema=%s AND table_name='users';"""
    with connection.cursor() as cursor:
        cursor.execute(queryFindTable, [dbName])
        selectResult = cursor.fetchall()
        return(selectResult)


# Returns values if email exists in table or None otherwise
def check_email_exist(connection, email):
    queryFindEmail = "SELECT email FROM users WHERE email=%(email)s;"
    with connection.cursor() as cursor:
        cursor.execute("USE " + connection.database + ";")
        cursor.execute(queryFindEmail, {"email": email})
        selectResult = cursor.fetchall()
        return(selectResult)
