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
		$query = "SELECT q.id, q.question_text, a.answer, a.correct 
		FROM " . $this->table . " q 
		INNER JOIN 
			answers a ON q.id = a.question_id 
		WHERE a.question_id IN (
			SELECT id FROM
				" . $this->table . " q
			WHERE q.category_id = 10 AND q.difficulty = 'medium'
		)";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

		return $stmt;

	}

}