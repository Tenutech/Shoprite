#!/usr/bin/env python3

import os
from datetime import datetime, timedelta
import mysql.connector

DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_DATABASE', 'your_db'),
    'port': int(os.getenv('DB_PORT', 3306))
}

DEFAULT_DAYS_LIMIT = 14

def get_days_limit():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    cursor.execute("SELECT value FROM settings WHERE `key` = 'auto_placed_back_in_talent_pool_fixed_term' LIMIT 1")
    result = cursor.fetchone()
    cursor.close()
    conn.close()
    return int(result[0]) if result else DEFAULT_DAYS_LIMIT

def main():
    days_limit = get_days_limit()
    cutoff = datetime.now() - timedelta(days=days_limit)

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    select_sql = """
        SELECT a.id
        FROM applicants a
        JOIN vacancy_fills vf ON a.appointed_id = vf.id
        WHERE a.employment = 'F'
        AND vf.created_at <= %s
    """
    cursor.execute(select_sql, (cutoff,))
    applicants = cursor.fetchall()

    count = 0
    for (applicant_id,) in applicants:
        update_sql = """
            UPDATE applicants
            SET appointed_id = NULL, shortlist_id = NULL
            WHERE id = %s
        """
        cursor.execute(update_sql, (applicant_id,))
        count += 1

    conn.commit()
    cursor.close()
    conn.close()

    print(f"{count} applicants moved back to the talent pool.")

if __name__ == '__main__':
    main()