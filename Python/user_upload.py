import sys
from user_defined_functions import *
import csv
import os.path
import re
import mysql.connector

# Collecting the list of arguments
arguments = get_CLI_options(sys.argv[1:])
# print(arguments)

if arguments.dry_run is not None:
    isDryRun = True
    print("ATTENTION! Script is running in a DRY_RUN mode.")
else:
    isDryRun = False

# *.csv file processing block
NEED_FILE_MESSAGE = """Script defined exception: File path or file name is required.
Please restart the script using correct parameters"""

if (arguments.create_table is not None):
    print("/* --- --create_table option has been given. Skip file processig. --- */")
else:
    print("/* --- Starting the file processing --- */")
    if arguments.file is None:
        sys.exit(NEED_FILE_MESSAGE)
    elif not os.path.exists(arguments.file):
        sys.exit("File " + arguments.file + " not found. Try using another file name")
    else:
        with open(arguments.file, mode='r') as csvFile:
            delimiterSymb = get_csv_delimiter(csvFile)
            resFile = []
            fileReader = csv.DictReader(csvFile,
                                        skipinitialspace=True,
                                        quoting=csv.QUOTE_NONE,
                                        fieldnames=["name", "surname", "email"],
                                        restkey="extra_cols",
                                        delimiter=delimiterSymb)
            for row in fileReader:
                resFile.append(row)

        # if the file was empty
        if resFile == []:
            sys.exit("File '{}' is empty. No records were found. Please, try another file.".format(arguments.file))

        # check for the header row
        if ((resFile[0]["name"] == "name")
           & (resFile[0]["surname"] == "surname")):
            resFile = resFile[1:]

        # if the file has had the header line only
        if resFile == []:
            sys.exit("File '{}' is empty. No records were found. Please, try another file.".format(arguments.file))

        # Processing names and surnames using regexp
        pattern = re.compile("[A-z]+(\'|-)[A-z]+|[A-z]+")
        for row in resFile:
            if (pattern.match(row["name"]) is not None):
                row["name"] = format_complex_name(
                            pattern.match(row["name"]).group())
            if (pattern.match(row["surname"]) is not None):
                row["surname"] = format_complex_name(
                            pattern.match(row["surname"]).group())

        # clear records with empty emails
        for row in resFile:
            if (row["email"] in (None, "")):
                resFile.remove(row)

        # Processing emails
        validEmails = 0
        invalidEmails = 0
        emailPattern = re.compile("([a-zA-Z0-9!.'*~?{}_-]+@\w+\.[a-zA-Z0-9.]+)")
        for row in resFile:
            if (emailPattern.match(row["email"]) is not None):
                row["email"] = emailPattern.match(row["email"]).group().lower()
                row["is_valid"] = True
                validEmails += 1
                print("Valid email: {}".format(row["email"]))
            else:
                row["is_valid"] = False
                invalidEmails += 1
                print("email {} is invalid".format(row["email"]))
        print("Valid emails: {}, invalid emails: {}".format(validEmails,
                                                            invalidEmails))
        print("/* --- The file processing end. --- */")

# checking DRY_RUN mode
if isDryRun:
    sys.exit("The script has been executed successfully.\n" +
             "The amount of valid user records prepared" +
             "to be inserted into the database is: {}".format(validEmails))

# Checking the connection parameters and requesting user input if required
connParamsDict = get_dbconnection_params(arguments, ["u", "p", "h", "n"])

# Checking "host" format as "host:port" or "host" only and assigning values
connParamsDict.update(get_host_port_split(connParamsDict["Host"]))

# Establishing the DB connection
try:
    print("/* --- Establishing MySQL DB connection. --- */")
    with mysql.connector.connect(
        host=connParamsDict["Host"],
        port=connParamsDict["Port"],
        user=connParamsDict["Username"],
        password=connParamsDict["Password"],
        database=connParamsDict["Database"]
    ) as connection:
        print("Connection to the Databse '{}'".format(connection.database) +
              " has been established succesfully")
        # connection.close()
        # with connection.cursor() as cursor:
        #     cursor.execute()
except mysql.connector.Error as e:
    print(e)
