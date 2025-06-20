import pandas as pd
import mysql.connector
import argparse
import json
from datetime import datetime
import os
import logging
from dotenv import load_dotenv
import sys

# --- Logging Setup ---
# Get the base directory (root of the project where this script resides)
base_dir = os.path.dirname(os.path.abspath(__file__))

# Define the log file path relative to the project root
log_file = os.path.join(base_dir, "../../storage/app/reports/users_export.log")

# Ensure the directory for the log file exists, create it if it doesn't
os.makedirs(os.path.dirname(log_file), exist_ok=True)

# Configure logging to capture timestamp, level, and message in the log file
logging.basicConfig(
    filename=log_file,
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s"
)

# --- Command-Line Argument Parsing ---
# Set up argument parser to handle command-line inputs
parser = argparse.ArgumentParser(description="Export applicants based on search.")
parser.add_argument("--auth_user", required=True, help="Authenticated user details")
parser.add_argument("--search", required=True, help="Search field")
args = parser.parse_args()

search = args.search.strip() if args.search else ""

# --- Environment Setup ---
# Retrieve database credentials from environment variables with defaults
DB_HOST = os.getenv('DB_HOST')
DB_PORT = os.getenv('DB_PORT', '3306') #ensure port is integer
DB_USER = os.getenv('DB_USERNAME')
DB_PASSWORD = os.getenv('DB_PASSWORD')
DB_NAME = os.getenv('DB_DATABASE')

# Define the output directory for the CSV file, relative to the project root
output_dir = os.path.join(base_dir, "../../storage/app/reports")
os.makedirs(output_dir, exist_ok=True)

# --- Database Connection ---
# Establish a connection to the MySQL database
connection = mysql.connector.connect(
    host=DB_HOST,
    port=DB_PORT,
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME
)

# Define the list of columns to select
select_columns = [
    "users.firstname",
    "users.lastname",
    "users.email",
    "users.phone",
    "users.id_number",
    "users.birth_date",
    "users.address",
    "users.age",
    "genders.name AS gender",
    "positions.name AS position",
    "stores.name AS store",
    "divisions.name AS division",
    "regions.name AS region",
    "brands.name AS brand",
    "users.internal"
]

# Define the FROM clause
from_clause = "FROM users"

# Build the base query
base_query = f"SELECT {', '.join(select_columns)} {from_clause}"

# Add LEFT JOINs
base_query += """
    LEFT JOIN genders ON users.gender_id = genders.id
    LEFT JOIN positions ON users.position_id = positions.id
    LEFT JOIN stores ON users.store_id = stores.id
    LEFT JOIN divisions ON users.division_id = divisions.id
    LEFT JOIN regions ON users.region_id = regions.id
    LEFT JOIN brands ON users.brand_id = brands.id
"""
# --- User Role ---
base_query += " WHERE users.role_id = 7"

# --- Conditional Search WHERE Clause ---
params = []
if search:
    base_query += """
    AND (
        users.firstname LIKE %s OR
        users.lastname LIKE %s OR
        users.email LIKE %s OR
        users.id_number LIKE %s OR
        users.phone LIKE %s
    )
    """
    params = [f"%{search}%"] * 5

# Add ORDER BY after WHERE (or immediately if no search)
base_query += " ORDER BY users.lastname"

logging.info(f"Search: {search if search else 'No search filter applied'}")

# --- Query Execution ---
# Execute the SQL query using pandas and parameterized inputs
data = pd.read_sql_query(base_query, connection, params=params if params else None)

# Optional: log number of rows
# print(f"Number of rows fetched: {len(data)}")

# --- Data Processing ---
mapped_data = []
for _, row in data.iterrows():
    internal_value = 'Yes' if row['internal'] == 1 else ('No' if row['internal'] == 2 else '')
    mapped_data.append([
        row['firstname'],
        row['lastname'],
        row['email'],
        row['phone'],
        row['id_number'],
        pd.to_datetime(row['birth_date']).strftime('%Y-%m-%d') if pd.notna(row['birth_date']) else '',
        row['address'],
        row['age'],
        row['gender'],
        row['position'],
        row['store'],
        row['division'],
        row['region'],
        row['brand'],
        internal_value
    ])

# Define column headers for the output CSV
columns = [
    "First Name",
    "Last Name",
    "Email",
    "Phone",
    "ID Number",
    "Date of Birth",
    "Address",
    "Age",
    "Gender",
    "Position",
    "Store",
    "Division",
    "Region",
    "Brand",
    "Internal"
]

# Create a pandas DataFrame from the mapped data
final_data = pd.DataFrame(mapped_data, columns=columns)

# --- File Output ---
# Generate a unique filename with a timestamp
output_file = f"{output_dir}/users_export_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
# Export the DataFrame to CSV without row indices
final_data.to_csv(output_file, index=False)

# Output the path to the generated file
print(output_file)

# --- Cleanup ---
# Close the database connection
connection.close()