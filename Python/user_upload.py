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
        iterationCounter = 1
        print("Rownum    | Message")
        for row in resFile:
            if (emailPattern.match(row["email"]) is not None):
                row["email"] = emailPattern.match(row["email"]).group().lower()
                row["is_valid"] = True
                validEmails += 1
                print("{}:    Valid email: {}".format(iterationCounter,row["email"]))
            else:
                row["is_valid"] = False
                invalidEmails += 1
                print("{}:    email {} is invalid".format(iterationCounter,row["email"]))
            iterationCounter += 1
        print("Valid emails: {}, invalid emails: {}".format(validEmails,
                                                            invalidEmails))
        # remove records with invalid emails from the array
        for row in resFile:
            if row["is_valid"] == False:
                resFile.remove(row)

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
        print("Connection to the Databse '{}'".format(connection.database)
              + " has been established succesfully")
        usersTableExists = (check_table_in_db(connection,
                            connection.database) != [])

        # if --create_table was given but the table already exists
        if ((arguments.create_table is not None) & usersTableExists):
            sys.exit("Table 'users' already exists on '{}' schema".
                     format(connection.database))

        # else if --create_table was given and the table doesn't exist
        elif ((arguments.create_table is not None) & (not usersTableExists)):
            print("/* --- Creating table ---*/")
            queryCreateTable = """ CREATE TABLE IF NOT EXISTS {}.users (
            id serial PRIMARY KEY NOT NULL,
            name VARCHAR(50) NOT NULL,
            surname VARCHAR(50) NOT NULL,
            email VARCHAR(350) UNIQUE NOT NULL);""".format(connection.database)
            # sys.exit(queryCreateTable)
            with connection.cursor() as cursor:
                if (cursor.execute(queryCreateTable) is None):
                    sys.exit(
                        "Table 'users' on schema '{}' created successfully.".
                        format(connection.database))

        # if --create_table was not given (preparing insert)
        # but the table does not exist
        elif ((arguments.create_table is None) & (not usersTableExists)):
            sys.exit("ATTENTION: There is no 'users' table "
                     + "in the specified database to insert data.\n"
                     + "           Please create table first "
                     + "using --create_table parameter""")
        # else: table exists, preparing insert
        else:
            print("Preparing INSERT")
            insertQuery = """ INSERT INTO {}.users (name, surname, email)
            VALUES (%(name)s,%(surname)s,%(email)s)""".format(
                                connection.database)
            cursor = connection.cursor()
            insertRecordCount = 0
            iterationCounter = 1
            print("Rownum    | Message")
            for row in resFile:
                # print(check_email_exist(connection,row["email"]))
                if (check_email_exist(connection,row["email"]) == []):
                    cursor.execute(insertQuery, row)
                    insertRecordCount += 1
                    print("{}:    User with Email '{}' inserted into the database"
                          .format(iterationCounter, row["email"]))
                else:
                    print("{}:    User with Email '{}' already exists"
                          .format(iterationCounter, row["email"]))
                iterationCounter += 1
            connection.commit()
            print("/* --- The script has been executed successfully.--- */\n"
                  + "{} records inserted into the database"
                  .format(insertRecordCount))
            cursor.close()
except mysql.connector.Error as e:
    print(e)
