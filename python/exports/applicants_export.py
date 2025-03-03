import pandas as pd
import mysql.connector
import argparse
import json
from datetime import datetime
import os
import logging
from dotenv import load_dotenv

# --- Logging Setup ---
# Get the base directory (root of the project where this script resides)
base_dir = os.path.dirname(os.path.abspath(__file__))

# Define the log file path relative to the project root
log_file = os.path.join(base_dir, "../../storage/app/reports/applicants_export.log")

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
parser = argparse.ArgumentParser(description="Export applicants based on filters.")
parser.add_argument("--auth_user", required=True, help="Authenticated user details")
parser.add_argument("--type", required=True, help="Filter type (all, region, division, store)")
parser.add_argument("--id", type=str, help="ID for region, division, or store")
parser.add_argument("--start_date", required=True, help="Start date (YYYY-MM-DD)")
parser.add_argument("--end_date", required=True, help="End date (YYYY-MM-DD)")
parser.add_argument("--max_distance", type=int, default=50, help="Maximum distance in kilometers")
parser.add_argument("--complete_state_id", required=True, type=int, help="ID for complete state")
parser.add_argument("--filters", required=True, help="JSON string of additional filters")
args = parser.parse_args()

# Parse the JSON filters string into a Python dictionary
filters = json.loads(args.filters)

# --- Environment Setup ---
# Load environment variables from a .env file located two levels up
env_path = os.path.join(base_dir, '../../.env')
load_dotenv(env_path)

# Retrieve database credentials from environment variables with defaults
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = os.getenv('DB_PORT', '3306')
DB_USER = os.getenv('DB_USERNAME', 'support')
DB_PASSWORD = os.getenv('DB_PASSWORD', 'Supp0rt01!')
DB_NAME = os.getenv('DB_DATABASE', 'shoprite')

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
    "applicants.created_at",
    "applicants.id_number",
    "applicants.firstname",
    "applicants.lastname",
    "applicants.birth_date",
    "applicants.age",
    "applicants.literacy_score",
    "applicants.literacy_questions",
    "applicants.numeracy_score",
    "applicants.numeracy_questions",
    "applicants.situational_score",
    "applicants.situational_questions",
    "applicants.score",
    "applicants.phone",
    "applicants.email",
    "applicants.location",
    "applicants.location_type",
    "applicants.terms_conditions",
    "applicants.public_holidays",
    "applicants.environment",
    "applicants.consent",
    "applicants.disability",
    "applicants.application_type",
    "applicants.state_id",
    "states.name AS state_name",
    "educations.name AS education_name",
    "durations.name AS duration_name",
    "genders.name AS gender_name",
    "races.name AS race_name",
    "towns.name AS town_name",
    "provinces.name AS province_name",
    """
    (
        SELECT GROUP_CONCAT(DISTINCT brands.name SEPARATOR ', ')
        FROM applicant_brands
        JOIN brands ON applicant_brands.brand_id = brands.id
        WHERE applicant_brands.applicant_id = applicants.id
    ) AS brand_names
    """,
    """
    (
        SELECT vacancy_fills.sap_number
        FROM vacancy_fills
        WHERE vacancy_fills.applicant_id = applicants.id
        ORDER BY vacancy_fills.created_at DESC
        LIMIT 1
    ) AS latest_sap_number
    """
]

# Add appointments.sap_number only when appointed is 'Yes'
if filters.get('appointed') == 'Yes':
    select_columns.insert(0, "appointments.sap_number")

# Define the FROM clause
from_clause = "FROM applicants"
if filters.get('appointed') == 'Yes':
    from_clause += " JOIN vacancy_fills AS appointments ON applicants.id = appointments.applicant_id"

# Build the base query
base_query = f"SELECT {', '.join(select_columns)} {from_clause}"

# Add LEFT JOINs
base_query += """
    LEFT JOIN states ON applicants.state_id = states.id
    LEFT JOIN educations ON applicants.education_id = educations.id
    LEFT JOIN durations ON applicants.duration_id = durations.id
    LEFT JOIN genders ON applicants.gender_id = genders.id
    LEFT JOIN races ON applicants.race_id = races.id
    LEFT JOIN towns ON applicants.town_id = towns.id
    LEFT JOIN provinces ON towns.province_id = provinces.id
"""

# Dynamic WHERE conditions
where_conditions = []
params = []

if filters.get('appointed') == 'Yes':
    where_conditions.append("appointments.created_at BETWEEN %s AND %s")
    params.extend([args.start_date, args.end_date])
    # Add geographic filters if present
    if filters.get('division_id') is not None:
        base_query += " JOIN stores ON appointments.store_id = stores.id"
        where_conditions.append("stores.division_id = %s")
        params.append(filters['division_id'])
    elif filters.get('region_id') is not None:
        base_query += " JOIN stores ON appointments.store_id = stores.id"
        where_conditions.append("stores.region_id = %s")
        params.append(filters['region_id'])
    elif filters.get('store_id') is not None:
        if isinstance(filters['store_id'], list):
            placeholders = ', '.join(['%s'] * len(filters['store_id']))
            where_conditions.append(f"appointments.store_id IN ({placeholders})")
            params.extend(filters['store_id'])
        else:
            where_conditions.append("appointments.store_id = %s")
            params.append(filters['store_id'])
elif filters.get('appointed') == 'No':
    where_conditions.append("applicants.created_at BETWEEN %s AND %s")
    where_conditions.append("""
        NOT EXISTS (
            SELECT 1 
            FROM vacancy_fills 
            WHERE vacancy_fills.applicant_id = applicants.id
        )
    """)
    params.extend([args.start_date, args.end_date])
else:
    where_conditions.append("applicants.created_at BETWEEN %s AND %s")
    params.extend([args.start_date, args.end_date])

# Apply additional filters from the filters dictionary
if filters.get('gender_id') is not None:
    where_conditions.append("applicants.gender_id = %s")
    params.append(filters['gender_id'])

if filters.get('race_id') is not None:
    where_conditions.append("applicants.race_id = %s")
    params.append(filters['race_id'])

if filters.get('education_id') is not None:
    where_conditions.append("applicants.education_id = %s")
    params.append(filters['education_id'])

if filters.get('duration_id') is not None:
    where_conditions.append("applicants.duration_id = %s")
    params.append(filters['duration_id'])

if filters.get('employment') is not None:
    where_conditions.append("applicants.employment = %s")
    params.append(filters['employment'])

if filters.get('min_age') is not None and filters.get('max_age') is not None:
    where_conditions.append("applicants.age BETWEEN %s AND %s")
    params.extend([filters['min_age'], filters['max_age']])

if filters.get('min_literacy') is not None and filters.get('max_literacy') is not None:
    where_conditions.append("applicants.literacy_score BETWEEN %s AND %s")
    params.extend([filters['min_literacy'], filters['max_literacy']])

if filters.get('min_numeracy') is not None and filters.get('max_numeracy') is not None:
    where_conditions.append("applicants.numeracy_score BETWEEN %s AND %s")
    params.extend([filters['min_numeracy'], filters['max_numeracy']])

if filters.get('min_situational') is not None and filters.get('max_situational') is not None:
    where_conditions.append("applicants.situational_score BETWEEN %s AND %s")
    params.extend([filters['min_situational'], filters['max_situational']])

if filters.get('completed') is not None:
    if filters['completed'] == 'Yes':
        where_conditions.append("applicants.state_id >= %s")
        params.append(args.complete_state_id)
    elif filters['completed'] == 'No':
        where_conditions.append("applicants.state_id < %s")
        params.append(args.complete_state_id)

if filters.get('shortlisted') is not None:
    if filters['shortlisted'] == 'Yes':
        where_conditions.append("applicants.shortlist_id IS NOT NULL")
        # Geographic filters for shortlisted applicants
        if filters.get('division_id') is not None:
            where_conditions.append("""
                EXISTS (
                    SELECT 1 
                    FROM shortlists 
                    JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE shortlists.id = applicants.shortlist_id 
                    AND stores.division_id = %s
                )
            """)
            params.append(filters['division_id'])
        elif filters.get('region_id') is not None:
            where_conditions.append("""
                EXISTS (
                    SELECT 1 
                    FROM shortlists 
                    JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE shortlists.id = applicants.shortlist_id 
                    AND stores.region_id = %s
                )
            """)
            params.append(filters['region_id'])
        elif filters.get('store_id') is not None:
            if isinstance(filters['store_id'], list):
                placeholders = ', '.join(['%s'] * len(filters['store_id']))
                where_conditions.append(f"""
                    EXISTS (
                        SELECT 1 
                        FROM shortlists 
                        JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                        WHERE shortlists.id = applicants.shortlist_id 
                        AND vacancies.store_id IN ({placeholders})
                    )
                """)
                params.extend(filters['store_id'])
            else:
                where_conditions.append("""
                    EXISTS (
                        SELECT 1 
                        FROM shortlists 
                        JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                        WHERE shortlists.id = applicants.shortlist_id 
                        AND vacancies.store_id = %s
                    )
                """)
                params.append(filters['store_id'])
    elif filters['shortlisted'] == 'No':
        where_conditions.append("applicants.shortlist_id IS NULL")

if filters.get('interviewed') is not None:
    if filters['interviewed'] == 'Yes':
        where_conditions.append("""
            EXISTS (
                SELECT 1 
                FROM interviews 
                WHERE interviews.applicant_id = applicants.id 
                AND interviews.score IS NOT NULL
            )
        """)
        # Geographic filters for interviewed applicants
        if filters.get('division_id') is not None:
            where_conditions.append("""
                EXISTS (
                    SELECT 1 
                    FROM interviews 
                    JOIN vacancies ON interviews.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE interviews.applicant_id = applicants.id 
                    AND stores.division_id = %s
                )
            """)
            params.append(filters['division_id'])
        elif filters.get('region_id') is not None:
            where_conditions.append("""
                EXISTS (
                    SELECT 1 
                    FROM interviews 
                    JOIN vacancies ON interviews.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE interviews.applicant_id = applicants.id 
                    AND stores.region_id = %s
                )
            """)
            params.append(filters['region_id'])
        elif filters.get('store_id') is not None:
            if isinstance(filters['store_id'], list):
                placeholders = ', '.join(['%s'] * len(filters['store_id']))
                where_conditions.append(f"""
                    EXISTS (
                        SELECT 1 
                        FROM interviews 
                        JOIN vacancies ON interviews.vacancy_id = vacancies.id
                        WHERE interviews.applicant_id = applicants.id 
                        AND vacancies.store_id IN ({placeholders})
                    )
                """)
                params.extend(filters['store_id'])
            else:
                where_conditions.append("""
                    EXISTS (
                        SELECT 1 
                        FROM interviews 
                        JOIN vacancies ON interviews.vacancy_id = vacancies.id
                        WHERE interviews.applicant_id = applicants.id 
                        AND vacancies.store_id = %s
                    )
                """)
                params.append(filters['store_id'])
    elif filters['interviewed'] == 'No':
        where_conditions.append("""
            (
                NOT EXISTS (
                    SELECT 1 
                    FROM interviews 
                    WHERE interviews.applicant_id = applicants.id
                ) 
                OR EXISTS (
                    SELECT 1 
                    FROM interviews 
                    WHERE interviews.applicant_id = applicants.id 
                    AND interviews.score IS NULL
                )
            )
        """)

# Append WHERE conditions to the base query if any exist
if where_conditions:
    base_query += " WHERE " + " AND ".join(where_conditions)

# Log the final query and parameters for debugging
# logging.info("Executing SQL query:\n" + base_query)
# logging.info("With parameters: " + str(params))

# --- Query Execution ---
# Execute the SQL query using pandas and parameterized inputs
data = pd.read_sql_query(base_query, connection, params=params)

# Log the number of rows retrieved
# logging.info(f"Number of rows fetched: {len(data)}")

# --- Data Processing ---
# Transform raw data into the desired output format
mapped_data = []
for _, row in data.iterrows():
    # Calculate percentage scores, avoiding division by zero
    literacy_percentage = (
        round((row['literacy_score'] / row['literacy_questions']) * 100)
        if row['literacy_questions'] > 0 else ''
    )
    numeracy_percentage = (
        round((row['numeracy_score'] / row['numeracy_questions']) * 100)
        if row['numeracy_questions'] > 0 else ''
    )
    situational_percentage = (
        round((row['situational_score'] / row['situational_questions']) * 100)
        if row['situational_questions'] > 0 else ''
    )
    total_questions = (
        (row['literacy_questions'] or 0)
        + (row['numeracy_questions'] or 0)
        + (row['situational_questions'] or 0)
    )
    assessment_score = (
        round((
            (row['literacy_score'] or 0)
            + (row['numeracy_score'] or 0)
            + (row['situational_score'] or 0)
        ) / total_questions * 100) if total_questions > 0 else ''
    )

    # Determine which SAP number to use: appointments.sap_number if appointed, else latest_sap_number
    sap_number = row['sap_number'] if filters.get('appointed') == 'Yes' and 'sap_number' in row else row['latest_sap_number']

    # Build the mapped row with formatted dates and calculated fields
    mapped_data.append([
        row['created_at'].strftime('%Y-%m-%d %H:%M:%S'),
        row['id_number'],
        row['firstname'],
        row['lastname'],
        row['birth_date'].strftime('%Y-%m-%d') if row['birth_date'] else '',
        row['age'],
        row['gender_name'],
        row['race_name'],
        row['phone'],
        row['email'],
        row['education_name'],
        row['duration_name'],
        row['town_name'],
        row['province_name'],
        row['brand_names'],
        row['location'],
        row['location_type'],
        row['terms_conditions'],
        row['public_holidays'],
        row['environment'],
        row['consent'],
        row['disability'],
        literacy_percentage,
        numeracy_percentage,
        situational_percentage,
        assessment_score,
        row['score'],
        row['application_type'],
        'Yes' if row['state_id'] < args.complete_state_id else 'No',
        row['state_name'],
        'Yes' if sap_number else 'No',
        sap_number
    ])

# Define column headers for the output CSV
columns = [
    'Application Date',
    'ID Number',
    'First Name',
    'Last Name',
    'Date of Birth',
    'Age',
    'Gender',
    'Race',
    'Phone Number',
    'Email Address',
    'Highest Qualification',
    'Experience',
    'Town',
    'Province',
    'Brands',
    'Home Address',
    'Location Type',
    'Terms & Conditions',
    'Shift Basis',
    'Work Environment',
    'Background Check',
    'Disability',
    'Literacy Score (%)',
    'Numeracy Score (%)',
    'Situational Awareness Score (%)',
    'Total Assessment Score (%)',
    'Overall Candidate Score',
    'Application Channel',
    'Drop off',
    'Workflow Stage',
    'Appointed',
    'SAP Number',
]

# Create a pandas DataFrame from the mapped data
final_data = pd.DataFrame(mapped_data, columns=columns)

# --- File Output ---
# Generate a unique filename with a timestamp
output_file = f"{output_dir}/applicants_export_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
# Export the DataFrame to CSV without row indices
final_data.to_csv(output_file, index=False)

# Output the path to the generated file
print(output_file)

# --- Cleanup ---
# Close the database connection
connection.close()