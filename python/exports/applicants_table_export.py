import pandas as pd
import mysql.connector
import argparse
from datetime import datetime
import os
import logging
from dotenv import load_dotenv
import sys
from contextlib import closing

# --- Constants ---
EMPLOYMENT_MAPPING = {
    'inconclusive': 'I',
    'active employee': 'A',
    'active': 'A',
    'blacklisted': 'B',
    'blacklist': 'B',
    'previously employed': 'P',
    'previously': 'P',
    'not an employee': 'N',
    'not employee': 'N',
    'fixed': 'F',
    'fixed term': 'F',
    'peak': 'S',
    'peak season': 'S',
    'yes': 'Y',
    'rrp': 'R',
}

# --- Helpers ---
def setup_logging():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    log_path = os.path.join(base_dir, "../../storage/app/reports/applicants_table_export.log")
    os.makedirs(os.path.dirname(log_path), exist_ok=True)
    logging.basicConfig(filename=log_path, level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s")
    return base_dir

def safe_div(numerator, denominator):
    if pd.notna(numerator) and pd.notna(denominator) and denominator != 0:
        return round((numerator / denominator) * 100)
    return ''

def map_employment_code_to_label(code: str) -> str:
    rev_map = {}
    for k, v in EMPLOYMENT_MAPPING.items():
        if v not in rev_map:
            rev_map[v] = k
    return rev_map.get(code.strip().upper(), '')

# --- Setup ---
base_dir = setup_logging()
load_dotenv()

parser = argparse.ArgumentParser(description="Export applicants based on search.")
parser.add_argument("--auth_user", required=True)
parser.add_argument("--search", required=True)
parser.add_argument("--complete_state_id", required=True, type=int)
args = parser.parse_args()
search = args.search.strip()

employment_code = map_employment_code_to_label(search)

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

# --- DB Config ---
db_config = {
    'host': os.getenv('DB_HOST'),
    'port': int(os.getenv('DB_PORT', 3306)),
    'user': os.getenv('DB_USERNAME'),
    'password': os.getenv('DB_PASSWORD'),
    'database': os.getenv('DB_DATABASE'),
}

# --- SQL Query ---
select_columns = [  # trimmed here for brevity — same as your current list
    "applicants.created_at", "applicants.employment", "applicants.id_number", "applicants.firstname",
    "applicants.lastname", "applicants.birth_date", "applicants.age", "applicants.literacy_score",
    "applicants.literacy_questions", "applicants.numeracy_score", "applicants.numeracy_questions",
    "applicants.situational_score", "applicants.situational_questions", "applicants.score", "applicants.phone",
    "applicants.email", "applicants.location", "applicants.location_type", "applicants.terms_conditions",
    "applicants.public_holidays", "applicants.environment", "applicants.consent", "applicants.disability",
    "applicants.application_type", "applicants.state_id", "states.name AS state_name",
    "educations.name AS education_name", "durations.name AS duration_name",
    "genders.name AS gender_name", "races.name AS race_name", "towns.name AS town_name",
    "provinces.name AS province_name",
    """(
        SELECT GROUP_CONCAT(DISTINCT brands.name SEPARATOR ', ')
        FROM applicant_brands
        JOIN brands ON applicant_brands.brand_id = brands.id
        WHERE applicant_brands.applicant_id = applicants.id
    ) AS brand_names""",
    """(
        SELECT vacancy_fills.sap_number
        FROM vacancy_fills
        WHERE vacancy_fills.applicant_id = applicants.id
        ORDER BY vacancy_fills.created_at DESC
        LIMIT 1
    ) AS latest_sap_number"""
]

base_query = f"SELECT {', '.join(select_columns)} FROM applicants"
joins = """
    LEFT JOIN states ON applicants.state_id = states.id
    LEFT JOIN educations ON applicants.education_id = educations.id
    LEFT JOIN durations ON applicants.duration_id = durations.id
    LEFT JOIN genders ON applicants.gender_id = genders.id
    LEFT JOIN races ON applicants.race_id = races.id
    LEFT JOIN towns ON applicants.town_id = towns.id
    LEFT JOIN provinces ON towns.province_id = provinces.id
"""
base_query += joins

params = []
if search:
    base_query += """
        WHERE
            applicants.firstname LIKE %s OR
            applicants.lastname LIKE %s OR
            applicants.id_number LIKE %s OR
            applicants.email LIKE %s OR
            applicants.phone LIKE %s OR
            applicants.state_id LIKE %s OR
            genders.name LIKE %s OR
            races.name LIKE %s OR
            applicants.employment = %s
    """
    params = [f"%{search}%"] * 8 + [map_employment_code_to_label(search)]

# --- Data Fetch ---
with closing(mysql.connector.connect(**db_config)) as connection:
    data = pd.read_sql_query(base_query, connection, params=params if params else None)

# --- Data Processing ---
mapped_data = []
for _, row in data.iterrows():

    # Calculate percentage scores, avoiding division by zero
    literacy_percentage = safe_div(row['literacy_score'], row['literacy_questions'])
    numeracy_percentage = safe_div(row['numeracy_score'], row['numeracy_questions'])
    situational_percentage = safe_div(row['situational_score'], row['situational_questions'])

    total_questions = sum([
        row['literacy_questions'], row['numeracy_questions'], row['situational_questions']
    ])
    total_score = row['literacy_score'] + row['numeracy_score'] + row['situational_score']
    assessment_score = safe_div(total_score, total_questions)

    sap_number = row.get('sap_number') or row.get('latest_sap_number')
    employment_code = map_employment_code_to_label(row.get('employment', ''))

    # Build the mapped row with formatted dates and calculated fields
    mapped_data.append([
        row['created_at'].strftime('%Y-%m-%d %H:%M:%S'),
        f"'{str(row['id_number'])}" if pd.notna(row['id_number']) else '', #Force string
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
        employment_code,
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
    'Employment Status',
    'SAP Number',
]

# Create a pandas DataFrame from the mapped data
final_df = pd.DataFrame(mapped_data, columns=columns)
output_dir = os.path.join(base_dir, "../../storage/app/reports")
os.makedirs(output_dir, exist_ok=True)
output_file = os.path.join(output_dir, f"applicants_table_export_{datetime.now():%Y%m%d_%H%M%S}.csv")
final_df.to_csv(output_file, index=False)

print(output_file)