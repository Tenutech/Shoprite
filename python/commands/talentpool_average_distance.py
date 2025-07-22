import os
from datetime import datetime, timedelta
import mysql.connector
from mysql.connector import Error
import sys

DB_CONFIG = {
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_DATABASE', 'your_db'),
    'port': int(os.getenv('DB_PORT', 3306))
}

def get_average_distance_talent_pool(connection, type_filter, id_filter, start_date, end_date, max_distance_km):
    # Retrieve stores based on the filter type (store, division, or region)
    store_query = "SELECT id, coordinates FROM stores"
    filters = []

    if type_filter == "store":
        filters.append(f"id = {id_filter}")
    elif type_filter == "division":
        filters.append(f"division_id = {id_filter}")
    elif type_filter == "region":
        filters.append(f"region_id = {id_filter}")

    if filters:
        store_query += " WHERE " + " AND ".join(filters)

    with connection.cursor(dictionary=True) as cursor:
        cursor.execute(store_query)
        stores = cursor.fetchall()

        if not stores:
            return 0.0

        # Retrieve the state ID corresponding to the 'complete' state.
        cursor.execute("SELECT id FROM states WHERE code = 'complete' LIMIT 1")
        state_row = cursor.fetchone()
        if not state_row:
            return 0.0

        complete_state_id = state_row['id']
        total_distance_km = 0
        total_applicant_count = 0

        for store in stores:
            coordinates = store.get('coordinates')
            if not coordinates:
                continue

            try:
                store_lat, store_lng = map(float, coordinates.split(','))
            except ValueError:
                continue

            # Query applicant stats using ST_Distance_Sphere
            applicant_query = """
                SELECT
                    SUM(ST_Distance_Sphere(
                        POINT(
                            SUBSTRING_INDEX(coordinates, ',', -1),
                            SUBSTRING_INDEX(coordinates, ',', 1)
                        ),
                        POINT(%s, %s)
                    )) AS total_distance,
                    COUNT(*) AS applicant_count
                FROM applicants
                WHERE created_at BETWEEN %s AND %s
                AND state_id >= %s
                AND ST_Distance_Sphere(
                        POINT(
                            SUBSTRING_INDEX(coordinates, ',', -1),
                            SUBSTRING_INDEX(coordinates, ',', 1)
                        ),
                        POINT(%s, %s)
                    ) <= %s
            """

            cursor.execute(applicant_query, (
                store_lng, store_lat,
                start_date, end_date,
                complete_state_id,
                store_lng, store_lat,
                max_distance_km * 1000
            ))

            result = cursor.fetchone()
            if result and result['applicant_count']:
                total_distance_km += (result['total_distance'] or 0) / 1000
                total_applicant_count += result['applicant_count']

    if total_applicant_count == 0:
        return 0.0

    return round(total_distance_km / total_applicant_count, 1)

def main():
    try:
        # Database connection
        with mysql.connector.connect(**DB_CONFIG) as connection:

            # Get the start date (first day of the same month, one year ago)
            end_date = datetime.today()
            start_date = (end_date.replace(day=1) - timedelta(days=365)).replace(day=1)

            # Retrieve the maximum allowed distance from settings or default to 50km
            with connection.cursor(dictionary=True) as cursor:
                cursor.execute("SELECT value FROM settings WHERE `key` = 'max_distance_from_store' LIMIT 1")
                setting = cursor.fetchone()
                max_distance = float(setting['value']) if setting and setting['value'] else 50.0

            # Calculate the average distance of talent pool applicants
            average_distance = get_average_distance_talent_pool(
                connection,
                type_filter='all',
                id_filter=None,
                start_date=start_date.strftime('%Y-%m-%d'),
                end_date=end_date.strftime('%Y-%m-%d'),
                max_distance_km=max_distance
            )

            # Update the statistics table where name = 'average_distance_talent_pool' and role_id is either 1 or 2
            update_query = """
                UPDATE statistics
                SET value = %s, updated_at = NOW()
                WHERE name = 'average_distance_talent_pool' AND role_id IN (1, 2)
            """

            with connection.cursor() as cursor:
                cursor.execute(update_query, (average_distance,))
                connection.commit()

                if cursor.rowcount > 0:
                    print(f"Updated {cursor.rowcount} row(s). New average distance: {average_distance} km")
                else:
                    print("No rows updated in statistics table.")

    except Error as e:
        print(f"MySQL Error: {e}", file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(f"Unexpected Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()