<?php
// Database connection setup
// https://www.youtube.com/watch?v=OEWXbpUMODk

Class Database {
	// DB Params
	private $host;
	private $db_name;
	private $username;
	private $password;
	private $conn;

	public function __construct($credentials) {

		$this->host = $credentials['host'];
		$this->db_name = $credentials['db_name'];
		$this->username = $credentials['username'];
		$this->password = $credentials['password'];
	}

	public function connect() {
		$this->conn = null;
		try {
			$this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
			// Set the error mode
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			trigger_error("Connection Error " . $e->getMessage(), E_USER_ERROR);
		}

		return $this->conn;
	}
}