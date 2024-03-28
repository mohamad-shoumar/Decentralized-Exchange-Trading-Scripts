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
        id INT AUTO_INCREMENT,
        token_id VARCHAR(255) NOT NULL,
        UNIQUE(token_id),
        PRIMARY KEY (id)
    );
    """
    try:
        cursor.execute(create_tokens_table)
        print("Table 'tokens' created successfully")
    except Error as e:
        print(f"The error '{e}' occurred")

def insert_token(connection, token_id):
    cursor = connection.cursor()
    insert_query = """
    INSERT INTO tokens (token_id)
    VALUES (%s) ON DUPLICATE KEY UPDATE token_id = token_id;
    """
    cursor.execute(insert_query, (token_id,))
    connection.commit()

def load_and_save_tokens(filename, connection):
    with open(filename, 'r') as file:
        tokens = file.read().splitlines()
        for token in tokens:
            insert_token(connection, token)
        print(f"{len(tokens)} tokens were inserted into the database.")

def update_database_with_new_token(token_id):
    conn = connect_to_database(config['host'], config['user'], config['password'], config['database'])
    if conn is not None:
        cursor = conn.cursor()
        cursor.execute("SELECT token_id FROM tokens WHERE token_id = %s", (token_id,))
        result = cursor.fetchone()
        if result is None:
            insert_token(conn, token_id)
        conn.close()

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
        
        load_and_save_tokens(tokens_file, conn)
    else:
        print("Failed to connect to the database")
