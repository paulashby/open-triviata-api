<?php

Class Question {
	// DB stuff
	private $conn;
	private $table = "questions";

	// Question properties
	public $id;
	public $question_text;
	public $category_id;
	public $type;
	public $difficulty;

	// categories table
	// public $category_name;

	// answers table
	public $question_id;
	public $answer;
	public $correct;

	// Constructor with DB
	public function __construct($db) {
		$this->conn = $db;
	}

	// Get questions
	public function read() {
		
		// Had to break this into two queries per https://stackoverflow.com/questions/24958367/php-pdo-using-mysql-variables
		// Filtering conditions such as difficulty = 'easy' go in the first query
		$query = "SET @randoms = (
    	SELECT GROUP_CONCAT(id) FROM (
        SELECT DISTINCT id FROM " . $this->table . " 
        ORDER BY RAND() 
        LIMIT 50
	    ) AS ids);";

	    // Prepare statement
	    $stmt = $this->conn->prepare($query);
	    // Execute query
	    $stmt->execute();

	    // Commented out q.category id for now - we may want it when filtering by category
		$query = "SELECT q.id, c.category, q.type, q.difficulty, q.question_text, a.answer, a.correct#, q.category_id
		FROM " . $this->table . " q
		INNER JOIN answers a ON q.id=a.question_id
		LEFT JOIN categories c ON q.category_id = c.id
		WHERE FIND_IN_SET(question_id, @randoms);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

		return $stmt;

	}

}