from flask import Flask, request, jsonify
import threading
import time
import json
import mysql.connector
import logging
import os
import glob

app = Flask(__name__)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Global variables
received_data = []
attendance_data = []  # Buffer for user attendance
data_lock = threading.Lock()
processed_ids = set()
last_antenna_values = {}  # Dictionary to store last known antenna values for each idHex

# MySQL database connection configuration from environment variables
db_config = {
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', 'Spica2024!'),
    'host': os.getenv('DB_HOST', '127.0.0.1'),
    'database': os.getenv('DB_NAME', 'rfid'),
}

def get_existing_antenna(idHex):
    """Retrieve the most recent antenna value from the database for the given idHex."""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        query = "SELECT antenna FROM test WHERE idHex = %s ORDER BY timestamp DESC LIMIT 1"
        cursor.execute(query, (idHex,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        return result[0] if result else None
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")
        return None

def is_rfid_tag_in_users(idHex):
    """Check if the RFID tag is associated with a user."""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        query = "SELECT id FROM users WHERE rfid_tag = %s"
        cursor.execute(query, (idHex,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        if result:
            logger.info(f"User ID {result[0]} found for RFID tag {idHex}")
            return result[0]  # Return the user ID if found
        logger.info(f"No user found for RFID tag {idHex}")
        return None
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")
        return None

def update_or_insert_data_to_db(data, attendance_data):
    """Update the existing records in the database or insert new ones if not present."""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()

        # Prepare SQL statements for `test` table
        insert_sql = """
        INSERT INTO test (idHex, antenna, eventNum, format, hostName, peakRssi, timestamp, type)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        update_sql = """
        UPDATE test
        SET antenna = %s, eventNum = %s, format = %s, hostName = %s, peakRssi = %s, type = %s
        WHERE idHex = %s AND timestamp = %s
        """

        # Prepare SQL statements for `user_attendance` table
        attendance_insert_sql = """
        INSERT INTO user_attendance (user_id, timestamp, antenna)
        VALUES (%s, %s, %s)
        """
        attendance_update_sql = """
        UPDATE user_attendance
        SET antenna = %s
        WHERE user_id = %s AND timestamp = %s
        """

        for record in data:
            entry_data = record['data']
            idHex = entry_data['idHex']
            timestamp = record['timestamp']
            new_antenna = entry_data['antenna']

            # Check if the record already exists in `test`
            cursor.execute("SELECT idHex, timestamp FROM test WHERE idHex = %s AND timestamp = %s", (idHex, timestamp))
            if cursor.fetchone() is None:
                # Insert new record
                cursor.execute(insert_sql, (
                    idHex, new_antenna, entry_data['eventNum'], entry_data['format'],
                    entry_data['hostName'], entry_data['peakRssi'], timestamp, record['type']
                ))
            else:
                # Update existing record
                cursor.execute(update_sql, (
                    new_antenna, entry_data['eventNum'], entry_data['format'],
                    entry_data['hostName'], entry_data['peakRssi'], record['type'],
                    idHex, timestamp
                ))

        # Insert or update user attendance data
        for attendance in attendance_data:
            user_id, timestamp, new_antenna = attendance
            cursor.execute("SELECT user_id, timestamp FROM user_attendance WHERE user_id = %s AND timestamp = %s", (user_id, timestamp))
            if cursor.fetchone() is None:
                cursor.execute(attendance_insert_sql, attendance)
            else:
                cursor.execute(attendance_update_sql, (new_antenna, user_id, timestamp))
            logger.info(f"Attendance logged for user ID {user_id} at {timestamp}")

        conn.commit()
        cursor.close()
        conn.close()
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")

def print_current_db_state():
    """Print the current state of the database."""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        query = "SELECT * FROM test ORDER BY timestamp DESC"
        cursor.execute(query)
        results = cursor.fetchall()
        for row in results:
            logger.info(row)
        cursor.close()
        conn.close()
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")

@app.route('/receive_data', methods=['POST'])
def receive_data():
    """Receive data from POST requests and store it in the global list."""
    global received_data
    data = request.json  # Get data from request
    logger.info("Received data:")
    logger.info(data)  # Print received data

    # Add data to global variable
    with data_lock:
        received_data.append(data)

    return jsonify({'message': 'Data successfully received.'}), 200

def process_data():
    """Process and filter data to include records where the antenna has changed or is detected by antenna 1 for new entries."""
    filtered_data = []
    attendance_buffer = []
    with data_lock:
        new_processed_ids = set()
        if received_data:
            for entry in received_data:
                for record in entry:
                    idHex = record['data']['idHex']
                    new_antenna = record['data']['antenna']
                    existing_antenna = last_antenna_values.get(idHex)

                    # Only add to filtered_data if it's a new entry on antenna 1 or an existing entry with antenna change
                    if existing_antenna is None and new_antenna == 1:
                        filtered_data.append(record)
                        new_processed_ids.add(idHex)
                        last_antenna_values[idHex] = new_antenna
                    elif existing_antenna is not None and existing_antenna != new_antenna:
                        filtered_data.append(record)
                        new_processed_ids.add(idHex)
                        last_antenna_values[idHex] = new_antenna

                        # Check if RFID tag is linked to a user
                        user_id = is_rfid_tag_in_users(idHex)
                        if user_id:
                            # Add attendance record to the buffer
                            attendance_buffer.append((user_id, record['timestamp'], new_antenna))
                            logger.info(f"Added to attendance buffer: user ID {user_id}, time {record['timestamp']}, antenna {new_antenna}")

            # Update processed IDs
            processed_ids.update(new_processed_ids)
            # Clear stored data after processing
            received_data.clear()
    return filtered_data, attendance_buffer

def clean_syslog_files():
    """Clean old syslog files."""
    syslog_files = glob.glob('/var/log/syslog*')
    for file in syslog_files:
        try:
            os.remove(file)
            logger.info(f"Removed old syslog file: {file}")
        except Exception as e:
            logger.error(f"Error removing syslog file {file}: {e}")

def print_and_store_data_periodically():
    """Periodically print and store data if there are changes in antenna values."""
    while True:
        time.sleep(10)  # Wait for 10 seconds
        filtered_data, attendance_data = process_data()
        if filtered_data or attendance_data:
            update_or_insert_data_to_db(filtered_data, attendance_data)
        print_current_db_state()  # Print current state of the database

def clean_logs_periodically():
    """Periodically clean syslog files."""
    while True:
        time.sleep(12 * 60 * 60)  # Wait for 12 hours
        clean_syslog_files()

def format_data_for_print(data):
    """Format the data for printing."""
    json_data = json.dumps(data, indent=2)
    formatted_data = json_data.replace('{\n    "data"', '\n{\n    "data"')
    return formatted_data

# Create and start threads for periodic tasks
data_thread = threading.Thread(target=print_and_store_data_periodically)
data_thread.daemon = True
data_thread.start()

log_thread = threading.Thread(target=clean_logs_periodically)
log_thread.daemon = True
log_thread.start()

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)  # Allows access from all network interfaces
