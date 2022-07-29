<?php

Class Question {
	
	private $table = "questions";
	private $conn;
	private $max_questions;
	private $token;

	// Constructor with DB
	public function __construct($db, $max_questions, $token) {
		$this->conn = $db;
		$this->max_questions = $max_questions;
		$this->token = $token;
	}

	// Get questions
	public function read($request_breakdown) {

		$where_clause = $this->buildWhereClause($request_breakdown['attributes']);

		$query = "SET @randoms = (
		SELECT GROUP_CONCAT(id) FROM (
		SELECT DISTINCT id FROM {$this->table}
		$where_clause 
		ORDER BY RAND() 
		LIMIT {$request_breakdown['amount']}
		) AS ids);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

		$query = "SELECT q.id, c.category, q.type, q.difficulty, q.question_text, a.answer, a.correct
		FROM " . $this->table . " q
		INNER JOIN answers a ON q.id=a.question_id
		INNER JOIN categories c ON q.category = c.id
		WHERE FIND_IN_SET(question_id, @randoms);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

		error_log("stmt is " . print_r($stmt, true));

		return $stmt;
	}

	private function buildWhereClause($attributes) {

		$where = "";

		if (count($attributes)) {
			$where = "WHERE ";
			$delimiter = "";

			foreach ($attributes as $attr_name => $attr_value) {
				$where .= "{$delimiter}{$attr_name}='{$attr_value}'";
				$delimiter = " AND ";
			}
		}

		if ($this->token !== false) {
			// Get previously-retrieved ids for this token
			$retrieved = $this->token->retrieved();

			if(strlen($retrieved) > 0) {
				//Exclude previously-retrieved ids from results
				if (strlen($where) === 0) {
					$where = "WHERE id NOT IN ($retrieved)";
				} else {
					$where .= "AND id NOT IN ($retrieved)";
				}
			}
		}
		return $where;
	}
}