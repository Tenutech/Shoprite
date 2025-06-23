# Import required modules
import pandas as pd  # For data manipulation and exporting to CSV
import mysql.connector  # For connecting to the MySQL database
import os  # For working with file paths and environment variables
from datetime import datetime  # For generating timestamps in filenames
from dotenv import load_dotenv  # To load environment variables from a .env file

# Load environment variables from the .env file
# The .env file is located two directories above this script's directory
base_dir = os.path.dirname(os.path.abspath(__file__))
env_path = os.path.join(base_dir, '../../.env')
load_dotenv(env_path)  # Load the .env file into environment variables

# Read database configuration from environment variables (or use defaults)
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')  # Database host
DB_PORT = os.getenv('DB_PORT', '3306')  # Database port
DB_USER = os.getenv('DB_USERNAME', 'support')  # Database username
DB_PASSWORD = os.getenv('DB_PASSWORD', 'Supp0rt01!')  # Database password
DB_NAME = os.getenv('DB_DATABASE', 'shoprite')  # Database name

# Define the output directory for storing the exported CSV report
output_dir = os.path.join(base_dir, '../../storage/app/reports')
os.makedirs(output_dir, exist_ok=True)  # Create the directory if it doesn't exist

# SQL query to retrieve user information along with related role, brand, store, region, and division
query = """
SELECT 
    users.firstname AS 'First Name',
    users.lastname AS 'Last Name',
    users.email AS 'Email',
    roles.name AS 'Role',
    brands.name AS 'Brand',
    stores.name AS 'Store',
    CONCAT("'", stores.code, "'") AS 'Branch Code 4',  -- Enclose code in quotes for Excel formatting
    CONCAT("'", stores.code_5, "'") AS 'Branch Code 5',  -- Same for code_5
    CONCAT("'", stores.code_6, "'") AS 'Branch Code 6',  -- Same for code_6
    regions.name AS 'Region',
    divisions.name AS 'Division'
FROM users
LEFT JOIN roles ON users.role_id = roles.id
LEFT JOIN regions ON users.region_id = regions.id
LEFT JOIN divisions ON users.division_id = divisions.id
LEFT JOIN stores ON users.store_id = stores.id
LEFT JOIN brands ON stores.brand_id = brands.id
WHERE users.role_id > 2 AND users.role_id < 7  -- Filter users with role_id between 3 and 6
ORDER BY users.role_id, users.firstname, users.lastname;  -- Sort output for readability
"""

# Establish a connection to the MySQL database using the provided credentials
connection = mysql.connector.connect(
    host=DB_HOST,
    port=DB_PORT,
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME
)

# Execute the SQL query and load the result into a Pandas DataFrame
df = pd.read_sql_query(query, connection)

# Define the output filename with a timestamp to avoid overwriting files
output_file = os.path.join(
    output_dir,
    f'users_export_{datetime.now().strftime("%Y%m%d_%H%M%S")}.csv'
)

# Export the DataFrame to a CSV file without including the DataFrame index
df.to_csv(output_file, index=False)

# Print the path to the output file for confirmation
print(output_file)

# Close the database connection
connection.close()