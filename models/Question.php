<?php

Class Question {
	
	private $table = "questions";
	private $conn;
	private $token;

	// Constructor with DB
	public function __construct($db, $token) {
		$this->conn = $db;
		$this->token = $token;
	}

	/**
	 * Route data to appropriate read method
	 *
	 * @param array $request_breakdown: parameter name/value pairs
	 * @return array of retrieved question data
	 */ 
	public function read($request_breakdown, $by_id) {
		return $by_id ? $this->readByID($request_breakdown) : $this->readRandom($request_breakdown);
	}

	// Get questions with provided ids
	private function readByID($request_breakdown) {

		$ids = $request_breakdown['ids'];
		$placeholder = implode(',', array_fill(0, count($ids), '?'));

		$query = "SELECT q.id, c.category, q.type, q.difficulty, q.question_text, a.answer, a.correct
		FROM questions q
		INNER JOIN answers a ON q.id=a.question_id
		INNER JOIN categories c ON q.category = c.id
		WHERE q.id IN($placeholder)
		ORDER BY FIELD(q.id,$placeholder);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query - include all ids for both placeholders
		$stmt->execute(array_merge($ids, $ids));

		return $stmt;	
	}

	// Get random questions
	private function readRandom($request_breakdown) {

		$attributes = $request_breakdown['attributes'];
		$where_clause = $this->buildWhereClause($attributes);

		if ($where_clause) {

			$query = "SET @randoms = (
			SELECT GROUP_CONCAT(id) FROM (
			SELECT DISTINCT id FROM questions
			WHERE $where_clause
			ORDER BY RAND() 
			LIMIT :amount
			) AS ids);";

			// Prepare statement
			$stmt = $this->conn->prepare($query);

			foreach ($attributes as $attribute_name => &$attribute_value) {
				$stmt->bindParam($attribute_name, $attribute_value, PDO::PARAM_STR);
			}

		} else {
			$query = "SET @randoms = (
			SELECT GROUP_CONCAT(id) FROM (
			SELECT DISTINCT id FROM questions
			ORDER BY RAND() 
			LIMIT :amount
			) AS ids);";

			// Prepare statement
			$stmt = $this->conn->prepare($query);
		}
		$stmt->bindParam('amount', $request_breakdown['amount'], PDO::PARAM_INT);

		$stmt->execute();

		$query = "SELECT q.id, c.category, q.type, q.difficulty, q.question_text, a.answer, a.correct
		FROM questions q
		INNER JOIN answers a ON q.id=a.question_id
		INNER JOIN categories c ON q.category = c.id
		WHERE FIND_IN_SET(question_id, @randoms);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

		return $stmt;
	}

	private function buildWhereClause($attributes) {

		$required = false;

		$where = "";

		if (count($attributes)) {
			$required = true;
			$delimiter = "";

			foreach ($attributes as $attr_name => $attr_value) {
				$where .= "{$delimiter}{$attr_name}=:$attr_name";
				$delimiter = " AND ";
			}
		}

		if ($this->token !== false) {
			// Get previously-retrieved ids for this token
			$retrieved = $this->token->retrieved();

			if(strlen($retrieved) > 0) {
				//Exclude previously-retrieved ids from results
				if (!$required) {
					$required = true;
					$where = "id NOT IN ($retrieved)";
				} else {
					$where .= " AND id NOT IN ($retrieved)";
				}
			}
		}
		return $required ? $where : false;
	}
}