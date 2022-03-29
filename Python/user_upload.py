import sys
from user_defined_functions import *
import csv
import os.path
import re
from email.utils import parseaddr

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

if arguments.create_table is None:
    print("--create_table option was not given. Starting the file processing")
    if arguments.file is None:
        sys.exit(NEED_FILE_MESSAGE)
    elif not os.path.exists(arguments.file):
        sys.exit("File " + arguments.file + " not found. Try using another file name")
    else:
        with open(arguments.file, mode='r') as csvFile:
            resFile = []
            fileReader = csv.DictReader(csvFile,
                                        skipinitialspace=True,
                                        quoting=csv.QUOTE_NONE,
                                        fieldnames=["name", "surname", "email"])
            for row in fileReader:
                resFile.append(row)

        # check for the header row
        if (resFile[0]["name"] == "name") & (resFile[0]["surname"] == "surname"):
            resFile = resFile[1:]

        # Processing names and surnames using regexp
        pattern = re.compile("[A-z]+(\'|-)[A-z]+|[A-z]+")
        for row in resFile:
            row["name"] = format_complex_name(
                            pattern.match(row["name"]).group())
            row["surname"] = format_complex_name(
                            pattern.match(row["surname"]).group())

        # Processing emails
        for row in resFile:
            if parseaddr(row["email"])[1] != "":
                row["email"] = parseaddr(row["email"])[1].lower()
                row["is_valid"] = True
            else:
                row["email"] = row["email"].lower()
                row["is_valid"] = False
