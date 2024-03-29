import time
import mysql.connector
from mysql.connector import Error
config = {
  'user': 'root',
  'password': '',
  'host': 'localhost',
  'port': 3306,
  'database': 'crypto_bulls'
}



def create_database(config):
    try:
        conn = mysql.connector.connect(
            host=config['host'],
            user=config['user'],
            passwd=config['password']
        )
        cursor = conn.cursor()
        cursor.execute(f"CREATE DATABASE IF NOT EXISTS {config['database']}")
        print(f"Database {config['database']} created successfully")
    except Error as e:
        print(f"The error '{e}' occurred")
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()
def connect_to_database(host_name, user_name, user_password, db_name):
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password,
            database=db_name
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")
    
    return connection

def create_table(connection):
    cursor = connection.cursor()
    create_tokens_table = """
    CREATE TABLE IF NOT EXISTS tokens (
        id INT(11) NOT NULL AUTO_INCREMENT,
        added_timestamp BIGINT(20) NOT NULL,
        token_id TEXT NOT NULL,
        tw_sub INT(11) NOT NULL DEFAULT 0,
        tw_tweets INT(11) NOT NULL DEFAULT 0,
        tw_days INT(11) NOT NULL DEFAULT 0,
        tg_sub INT(11) NOT NULL DEFAULT 0,
        liqlock VARCHAR(10) NOT NULL,
        mintaut VARCHAR(10) NOT NULL,
        mutable VARCHAR(10) NOT NULL,
        topholder VARCHAR(10) NOT NULL,
        score VARCHAR(10) NOT NULL,
        twitter TEXT NOT NULL,
        telegram TEXT NOT NULL,
        website TEXT NOT NULL,
        runner INT(11) DEFAULT 1,
        fatto_social INT(11) NOT NULL DEFAULT 0,
        fatto_rug INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    """
    try:
        cursor.execute(create_tokens_table)
        print("Table 'tokens' created successfully")
    except Error as e:
        print(f"The error '{e}' occurred")


def update_database_with_new_token(token_id):
    try:
        conn = connect_to_database(config['host'], config['user'], config['password'], config['database'])
        print('conn: ', conn)
        if conn is not None:
            unix_timestamp = int(time.time())  
            print('unix_timestamp: ', unix_timestamp)
            print('token: ', token_id)
            cursor = conn.cursor()
            cursor.execute("SELECT token_id FROM tokens WHERE token_id = %s", (token_id,))
            result = cursor.fetchone()
            print('result: ', result)
            if result is None:
                insert_query = """
                INSERT INTO tokens (token_id, added_timestamp)
                VALUES (%s, %s) ON DUPLICATE KEY UPDATE token_id = token_id, added_timestamp = VALUES(added_timestamp);
                """
                cursor.execute(insert_query, (token_id, unix_timestamp))
                conn.commit()
            conn.close()
    except Exception as e:
        print(f"Error inserting token {token_id}: {e}")
def remove_token_from_database(token_id):
    conn = connect_to_database(config['host'], config['user'], config['password'], config['database'])
    if conn is not None:
        cursor = conn.cursor()
        cursor.execute("DELETE FROM tokens WHERE token_id = %s", (token_id,))
        conn.commit()
        conn.close()

if __name__ == "__main__":
    host = "127.0.0.1"
    user = "root"
    password = "" 
    database = "crypto_bulls"

    conn = connect_to_database(config['host'], config['user'], config['password'], config['database'])

    if conn is not None:
        create_table(conn)

        tokens_file = "seen_tokens.txt"
        
    else:
        print("Failed to connect to the database")
