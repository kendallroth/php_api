<?php
require_once("/home/cabox/workspace/constants.php");
require_once(DATABASE_CONSTANTS);

/**
 * @brief	Class for common database interactions
 */
class DB {
	/**
	 * @brief	Retrieve a database connection
	 * @return	Connection to the database
	 */
	static function connect() {
		//Create the connection to the database
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		
		//Display database connection errors
		if ( $conn->connect_error ) {
			//TODO: Come up with a better of displaying database errors
			die("ERROR: Connection Error -> " . $conn->connect_error);
		}
		
		return $conn;
	}
}
