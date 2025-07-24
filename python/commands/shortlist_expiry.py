import os
import json
from datetime import datetime, timedelta
import mysql.connector
from mysql.connector import Error
import sys

DB_CONFIG = {
    'host': os.getenv('DB_HOST'),
    'user': os.getenv('DB_USERNAME'),
    'password': os.getenv('DB_PASSWORD'),
    'database': os.getenv('DB_DATABASE'),
    'port': int(os.getenv('DB_PORT', 3306))
}

# Gets `shortlist_expiry` value from settings table else uses 14 days as default
def fetch_setting(connection, key, default):
    with connection.cursor(dictionary=True) as cursor:
        cursor.execute("SELECT value FROM settings WHERE `key` = %s", (key,))
        result = cursor.fetchone()
        return int(result['value']) if result and result['value'].isdigit() else default

# Fetch only the shortlists where the expiry period has passed
def fetch_expired_shortlists(connection, expiry_days):
    cutoff_date = (datetime.now() - timedelta(days=expiry_days)).strftime('%Y-%m-%d %H:%M:%S')
    with connection.cursor(dictionary=True) as cursor:
        cursor.execute("SELECT * FROM shortlists WHERE updated_at <= %s", (cutoff_date,))
        return cursor.fetchall()

# Fetch all applicants
def fetch_applicant(connection, applicant_id):
    with connection.cursor(dictionary=True) as cursor:
        cursor.execute("SELECT * FROM applicants WHERE id = %s", (applicant_id,))
        return cursor.fetchone()

# Check if an interview exists for the applicant within the expiry period
def fetch_interviews(connection, applicant_id, vacancy_id):
    with connection.cursor(dictionary=True) as cursor:
        cursor.execute("""
            SELECT * FROM interviews
            WHERE applicant_id = %s AND vacancy_id = %s
            ORDER BY scheduled_date ASC
        """, (applicant_id, vacancy_id))
        return cursor.fetchall()

def update_applicant_shortlist(connection, applicant_id):
    with connection.cursor() as cursor:
        cursor.execute("""
            UPDATE applicants
            SET shortlist_id = NULL,
                updated_at = NOW()
            WHERE id = %s AND appointed_id is NULL
        """, (applicant_id,))

def update_shortlist_applicants(connection, shortlist_id, applicant_ids):
    with connection.cursor() as cursor:
        cursor.execute("""
            UPDATE shortlists
            SET applicant_ids = %s,
                updated_at = NOW()
            WHERE id = %s
        """, (json.dumps(applicant_ids), shortlist_id))

def process_shortlists(connection):
    expiry_days = fetch_setting(connection, 'shortlist_expiry', 14)
    shortlists = fetch_expired_shortlists(connection, expiry_days)

    removed_applicants_count = 0

    for shortlist in shortlists:
        try:
            applicant_ids = json.loads(shortlist['applicant_ids'] or '[]')
        except json.JSONDecodeError:
            continue

        updated_ids = []

        for applicant_id in applicant_ids:
            remove = True
            interviews = fetch_interviews(connection, applicant_id, shortlist['vacancy_id'])

            if interviews:
                latest_interview = interviews[-1]

                if latest_interview['status'] in [
                  'Scheduled', 'Confirmed', 'Reschedule', 'Completed', 'Appointed'
                ]:
                    remove = False

            if remove:
                applicant = fetch_applicant(connection, applicant_id)
                if (
                    applicant and
                    applicant['shortlist_id'] == shortlist['id'] and
                    applicant['appointed_id'] is None
                ):
                    update_applicant_shortlist(connection, applicant_id)
                    removed_applicants_count += 1
            else:
                updated_ids.append(applicant_id)

        if json.dumps(updated_ids) != shortlist['applicant_ids']:
            update_shortlist_applicants(connection, shortlist['id'], updated_ids)

    connection.commit()

    return removed_applicants_count

def main():
    try:
        with mysql.connector.connect(**DB_CONFIG) as connection:
            updated = process_shortlists(connection)

            if updated > 0:
                print(f"{updated} applicants(s) were removed from shortlists that had no interviews scheduled.")
            else:
                print("No applicants required removing.")

    except Error as e:
        print(f"MySQL Error: {e}", file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(f"Unexpected Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()