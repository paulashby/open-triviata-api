<?php

Class Question {
	
	private const MAX_QUESTIONS = 50;
	
	private $conn;
	private $table = "questions";

	// Constructor with DB
	public function __construct($db) {
		$this->conn = $db;
	}

	// Get questions
	public function read($request_breakdown) {

		$where_clause = $this->buildWhereClause($request_breakdown['attributes']);
		// Null coalescing operator
		$limit = $request_breakdown['amount'] ?? self::MAX_QUESTIONS;

		$query = "SET @randoms = (
    	SELECT GROUP_CONCAT(id) FROM (
        SELECT DISTINCT id FROM {$this->table}
        $where_clause 
        ORDER BY RAND() 
        LIMIT $limit
	    ) AS ids);";

	    // Prepare statement
	    $stmt = $this->conn->prepare($query);
	    // Execute query
	    $stmt->execute();

	    // !!!! JOINS - INNER JOIN will return only items with corresponding entries (via FOREIGN KEY) in "right" table
	    // !!!! JOINS - LEFT JOIN will return all matched entries in "left" table, regardless of whether they have corresponding entry (via FOREIGN KEY) in "right" table
	    // https://www.sqlshack.com/learn-sql-inner-join-vs-left-join/
	    // So might be worth checking this query...

		$query = "SELECT q.id, c.category, q.type, q.difficulty, q.question_text, a.answer, a.correct
		FROM " . $this->table . " q
		INNER JOIN answers a ON q.id=a.question_id
		LEFT JOIN categories c ON q.category = c.id
		WHERE FIND_IN_SET(question_id, @randoms);";

		// Prepare statement
		$stmt = $this->conn->prepare($query);
		// Execute query
		$stmt->execute();

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
		/*
			Need to make sure we're not serving previously-served questions when request includes a token
			AND id NOT IN ( 1008, 1009, 1010, 1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019, 1020, 1021, 1022, 1023, 1024, 1025, 1026, 1027, 1028, 1029, 1030, 1031, 1032, 1033, 1034);
			 1008, 1009, 1010, 1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019, 1020, 1021, 1022, 1023, 1024, 1025, 1026, 1027, 1028, 1029, 1030, 1031, 1032, 1033, 1034 

		*/
		return $where;
	}

}