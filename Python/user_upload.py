import getopt
import sys
import argparse
from user_defined_functions import *
import csv
import os.path

# Collecting the list of arguments
arguments = get_CLI_options(sys.argv[1:])
print(arguments)

if arguments.dry_run is not None:
    isDryRun = True
    print("ATTENTION! Script is running in a DRY_RUN mode.")
else:
    isDryRun = False

# *.csv file processing block
NEED_FILE_MESSAGE = """Script defined exception: File path or file name is required.
Please restart the script using correct parameters"""

if arguments.create_table is None:
    print("no create_table option. starting file processing")
    if arguments.file is None:
        sys.exit(NEED_FILE_MESSAGE)
    elif not os.path.exists(arguments.file):
        sys.exit("File " + arguments.file + " not found. Try using another file name")
    else:
        print(os.path.abspath(arguments.file))
