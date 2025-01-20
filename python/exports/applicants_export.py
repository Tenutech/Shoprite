import pandas as pd
import mysql.connector
import argparse
import json
from datetime import datetime
import os
import logging
from dotenv import load_dotenv

# Set up logging
# Get the base directory (root of the project)
base_dir = os.path.dirname(os.path.abspath(__file__))

# Set the log file path relative to the project root
log_file = os.path.join(base_dir, "../../storage/app/reports/applicants_export.log")

# Ensure the directory for the log file exists
os.makedirs(os.path.dirname(log_file), exist_ok=True)

# Set up logging
logging.basicConfig(
    filename=log_file,
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s"
)

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Export applicants based on filters.")
parser.add_argument("--auth_user", required=True, help="Auth user details")
parser.add_argument("--type", required=True, help="Type of filter (all, region, division, store)")
parser.add_argument("--id", type=str, help="ID for region, division, or store")
parser.add_argument("--start_date", required=True, help="Start date for filtering")
parser.add_argument("--end_date", required=True, help="End date for filtering")
parser.add_argument("--max_distance", type=int, default=50, help="Maximum distance in kilometers")
parser.add_argument("--complete_state_id", required=True, type=int, help="ID for the complete state")
parser.add_argument("--filters", required=True, help="Additional filters as JSON")
args = parser.parse_args()

# Parse filters JSON
filters = json.loads(args.filters)

# Get the base directory (root of the project)
base_dir = os.path.dirname(os.path.abspath(__file__))

# Load the .env file
env_path = os.path.join(base_dir, '../../.env')
load_dotenv(env_path)

# Database connection details from the .env file
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = os.getenv('DB_PORT', '3306')
DB_USER = os.getenv('DB_USERNAME', 'support')
DB_PASSWORD = os.getenv('DB_PASSWORD', 'Supp0rt01!')
DB_NAME = os.getenv('DB_DATABASE', 'shoprite')

# Get the base directory (root of the project)
base_dir = os.path.dirname(os.path.abspath(__file__))

# Set the output directory relative to the project root
output_dir = os.path.join(base_dir, "../../storage/app/reports")
os.makedirs(output_dir, exist_ok=True)

# Connect to the database
connection = mysql.connector.connect(
    host=DB_HOST,
    port=DB_PORT,
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME
)

# Build base query
base_query = """
    SELECT 
        applicants.created_at,
        applicants.id_number,
        applicants.firstname,
        applicants.lastname,
        applicants.birth_date,
        applicants.age,
        applicants.literacy_score,
        applicants.literacy_questions,
        applicants.numeracy_score,
        applicants.numeracy_questions,
        applicants.situational_score,
        applicants.situational_questions,
        applicants.score,
        applicants.phone,
        applicants.email,
        applicants.location,
        applicants.location_type,
        applicants.terms_conditions,
        applicants.public_holidays,
        applicants.environment,
        applicants.consent,
        applicants.disability,
        applicants.application_type,
        applicants.state_id,
        states.name AS state_name,
        educations.name AS education_name,
        durations.name AS duration_name,
        genders.name AS gender_name,
        races.name AS race_name,
        towns.name AS town_name,
        provinces.name AS province_name,
        (
            SELECT GROUP_CONCAT(DISTINCT brands.name SEPARATOR ', ')
            FROM applicant_brands
            JOIN brands ON applicant_brands.brand_id = brands.id
            WHERE applicant_brands.applicant_id = applicants.id
        ) AS brand_names,
        (
            SELECT vacancy_fills.sap_number
            FROM vacancy_fills
            WHERE vacancy_fills.applicant_id = applicants.id
            ORDER BY vacancy_fills.created_at DESC
            LIMIT 1
        ) AS latest_sap_number
    FROM applicants
    LEFT JOIN states ON applicants.state_id = states.id
    LEFT JOIN educations ON applicants.education_id = educations.id
    LEFT JOIN durations ON applicants.duration_id = durations.id
    LEFT JOIN genders ON applicants.gender_id = genders.id
    LEFT JOIN races ON applicants.race_id = races.id
    LEFT JOIN towns ON applicants.town_id = towns.id
    LEFT JOIN provinces ON towns.province_id = provinces.id
    WHERE applicants.created_at BETWEEN %s AND %s
"""

# Query parameters
params = [args.start_date, args.end_date]

# Apply filters
if filters.get('gender_id') is not None:
    base_query += " AND applicants.gender_id = %s"
    params.append(filters['gender_id'])

if filters.get('race_id') is not None:
    base_query += " AND applicants.race_id = %s"
    params.append(filters['race_id'])

if filters.get('education_id') is not None:
    base_query += " AND applicants.education_id = %s"
    params.append(filters['education_id'])

if filters.get('duration_id') is not None:
    base_query += " AND applicants.duration_id = %s"
    params.append(filters['duration_id'])

if filters.get('employment') is not None:
    base_query += " AND applicants.employment = %s"
    params.append(filters['employment'])

if filters.get('min_age') is not None and filters.get('max_age') is not None:
    base_query += " AND applicants.age BETWEEN %s AND %s"
    params.extend([filters['min_age'], filters['max_age']])

if filters.get('min_literacy') is not None and filters.get('max_literacy') is not None:
    base_query += " AND applicants.literacy_score BETWEEN %s AND %s"
    params.extend([filters['min_literacy'], filters['max_literacy']])

if filters.get('min_numeracy') is not None and filters.get('max_numeracy') is not None:
    base_query += " AND applicants.numeracy_score BETWEEN %s AND %s"
    params.extend([filters['min_numeracy'], filters['max_numeracy']])

if filters.get('min_situational') is not None and filters.get('max_situational') is not None:
    base_query += " AND applicants.situational_score BETWEEN %s AND %s"
    params.extend([filters['min_situational'], filters['max_situational']])

if filters.get('completed') is not None:
    if filters['completed'] == 'Yes':
        base_query += " AND applicants.state_id >= %s"
        params.append(args.complete_state_id)
    elif filters['completed'] == 'No':
        base_query += " AND applicants.state_id < %s"
        params.append(args.complete_state_id)

if filters.get('shortlisted') is not None:
    if filters['shortlisted'] == 'Yes':
        base_query += " AND applicants.shortlist_id IS NOT NULL"
        
        # Apply geographic filters for shortlisted applicants
        if filters.get('division_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM shortlists 
                    JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE shortlists.id = applicants.shortlist_id 
                    AND stores.division_id = %s
                )
            """
            params.append(filters['division_id'])
        elif filters.get('region_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM shortlists 
                    JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE shortlists.id = applicants.shortlist_id 
                    AND stores.region_id = %s
                )
            """
            params.append(filters['region_id'])
        elif filters.get('store_id') is not None:
            if isinstance(filters['store_id'], list):
                placeholders = ', '.join(['%s'] * len(filters['store_id']))
                base_query += f"""
                    AND EXISTS (
                        SELECT 1 
                        FROM shortlists 
                        JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                        WHERE shortlists.id = applicants.shortlist_id 
                        AND vacancies.store_id IN ({placeholders})
                    )
                """
                params.extend(filters['store_id'])
            else:
                base_query += """
                    AND EXISTS (
                        SELECT 1 
                        FROM shortlists 
                        JOIN vacancies ON shortlists.vacancy_id = vacancies.id
                        WHERE shortlists.id = applicants.shortlist_id 
                        AND vacancies.store_id = %s
                    )
                """
                params.append(filters['store_id'])
    elif filters['shortlisted'] == 'No':
        base_query += " AND applicants.shortlist_id IS NULL"

if filters.get('interviewed') is not None:
    if filters['interviewed'] == 'Yes':
        # Case: Interviewed is 'Yes'
        base_query += """
            AND EXISTS (
                SELECT 1 
                FROM interviews 
                WHERE interviews.applicant_id = applicants.id 
                AND interviews.score IS NOT NULL
            )
        """
        
        # Apply geographic filters for interviewed applicants
        if filters.get('division_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM interviews 
                    JOIN vacancies ON interviews.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE interviews.applicant_id = applicants.id 
                    AND stores.division_id = %s
                )
            """
            params.append(filters['division_id'])
        elif filters.get('region_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM interviews 
                    JOIN vacancies ON interviews.vacancy_id = vacancies.id
                    JOIN stores ON vacancies.store_id = stores.id
                    WHERE interviews.applicant_id = applicants.id 
                    AND stores.region_id = %s
                )
            """
            params.append(filters['region_id'])
        elif filters.get('store_id') is not None:
            if isinstance(filters['store_id'], list):
                placeholders = ', '.join(['%s'] * len(filters['store_id']))
                base_query += f"""
                    AND EXISTS (
                        SELECT 1 
                        FROM interviews 
                        JOIN vacancies ON interviews.vacancy_id = vacancies.id
                        WHERE interviews.applicant_id = applicants.id 
                        AND vacancies.store_id IN ({placeholders})
                    )
                """
                params.extend(filters['store_id'])
            else:
                base_query += """
                    AND EXISTS (
                        SELECT 1 
                        FROM interviews 
                        JOIN vacancies ON interviews.vacancy_id = vacancies.id
                        WHERE interviews.applicant_id = applicants.id 
                        AND vacancies.store_id = %s
                    )
                """
                params.append(filters['store_id'])

    elif filters['interviewed'] == 'No':
        # Case: Interviewed is 'No'
        base_query += """
            AND (
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
        """

if filters.get('appointed') is not None:
    if filters['appointed'] == 'Yes':
        # Include only appointed applicants
        base_query += " AND applicants.appointed_id IS NOT NULL"
        
        # Apply the date range to vacancy_fills.created_at
        base_query += """
            AND EXISTS (
                SELECT 1 
                FROM vacancy_fills 
                WHERE vacancy_fills.applicant_id = applicants.id 
                AND vacancy_fills.created_at BETWEEN %s AND %s
            )
        """
        params.extend([args.start_date, args.end_date])
        
        # Apply geographic filters for appointed applicants
        if filters.get('division_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM vacancy_fills 
                    JOIN stores ON vacancy_fills.store_id = stores.id 
                    WHERE vacancy_fills.applicant_id = applicants.id 
                    AND stores.division_id = %s
                )
            """
            params.append(filters['division_id'])
        elif filters.get('region_id') is not None:
            base_query += """
                AND EXISTS (
                    SELECT 1 
                    FROM vacancy_fills 
                    JOIN stores ON vacancy_fills.store_id = stores.id 
                    WHERE vacancy_fills.applicant_id = applicants.id 
                    AND stores.region_id = %s
                )
            """
            params.append(filters['region_id'])
        elif filters.get('store_id') is not None:
            if isinstance(filters['store_id'], list):
                placeholders = ', '.join(['%s'] * len(filters['store_id']))
                base_query += f"""
                    AND EXISTS (
                        SELECT 1 
                        FROM vacancy_fills 
                        WHERE vacancy_fills.applicant_id = applicants.id 
                        AND vacancy_fills.store_id IN ({placeholders})
                    )
                """
                params.extend(filters['store_id'])
            else:
                base_query += """
                    AND EXISTS (
                        SELECT 1 
                        FROM vacancy_fills 
                        WHERE vacancy_fills.applicant_id = applicants.id 
                        AND vacancy_fills.store_id = %s
                    )
                """
                params.append(filters['store_id'])

        if ((filters.get('shortlisted') is None or filters['shortlisted'] == 'No') and (filters.get('interviewed') is None or filters['interviewed'] == 'No') and (filters.get('appointed') is None or filters['appointed'] == 'No') and (filters.get('store_id') is not None or filters.get('region_id') is not None or filters.get('division_id') is not None)):
            # Build the store query
            store_query = "SELECT id, coordinates FROM stores WHERE 1=1"
            store_params = []

            if filters.get('division_id') is not None:
                store_query += " AND division_id = %s"
                store_params.append(filters['division_id'])
            elif filters.get('region_id') is not None:
                store_query += " AND region_id = %s"
                store_params.append(filters['region_id'])
            elif filters.get('store_id') is not None:
                if isinstance(filters['store_id'], list):
                    placeholders = ', '.join(['%s'] * len(filters['store_id']))
                    store_query += f" AND id IN ({placeholders})"
                    store_params.extend(filters['store_id'])
                else:
                    store_query += " AND id = %s"
                    store_params.append(filters['store_id'])

            # Execute store query
            store_df = pd.read_sql_query(store_query, connection, params=store_params)

            # Check if stores are empty
            if store_df.empty:
                final_data = pd.DataFrame(columns=columns)
                output_file = f"{output_dir}/applicants_export_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
                final_data.to_csv(output_file, index=False)
                print(output_file)
                connection.close()
                exit()

            # Proximity filtering
            proximity_queries = []
            for _, store in store_df.iterrows():
                if store['coordinates']:
                    lat, lng = map(float, store['coordinates'].split(','))
                    proximity_query = f"""
                        SELECT applicants.* 
                        FROM applicants 
                        WHERE ST_Distance_Sphere(
                            POINT(SUBSTRING_INDEX(applicants.coordinates, ',', -1), SUBSTRING_INDEX(applicants.coordinates, ',', 1)),
                            POINT(%s, %s)
                        ) <= %s
                    """
                    proximity_params = [lng, lat, args.max_distance * 1000]
                    proximity_queries.append((proximity_query, proximity_params))

            # Combine proximity queries
            if proximity_queries:
                union_query = " UNION ".join([q[0] for q in proximity_queries])
                union_params = [param for q in proximity_queries for param in q[1]]

                # Execute the combined query
                proximity_df = pd.read_sql_query(union_query, connection, params=union_params)

                # Merge the proximity result with the main query
                data = pd.merge(data, proximity_df, how='inner', on='id')

    else:
        # Default date range filter for non-appointed applicants
        base_query += " AND applicants.created_at BETWEEN %s AND %s"
        params.extend([args.start_date, args.end_date])

# Execute the query
data = pd.read_sql_query(base_query, connection, params=params)

# Build the query string for logging
query_with_params = base_query
for param in params:
    if isinstance(param, str):
        query_with_params = query_with_params.replace('%s', f"'{param}'", 1)
    else:
        query_with_params = query_with_params.replace('%s', str(param), 1)

# Log the constructed query
# logging.info("Executing SQL query:")
# logging.info(query_with_params)

# Map columns and calculate additional fields
mapped_data = []
for _, row in data.iterrows():
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
        'Yes' if row['latest_sap_number'] else 'No',
        row['latest_sap_number']
    ])

# Convert mapped data to a DataFrame
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
final_data = pd.DataFrame(mapped_data, columns=columns)

# Save to CSV
output_file = f"{output_dir}/applicants_export_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
final_data.to_csv(output_file, index=False)

# Output the file path
print(output_file)

connection.close()