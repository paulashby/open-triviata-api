<?php
	// Headers
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");

	include_once "../config/Database.php";
	include_once "../models/question.php";

	// Instantiate database and connect
	$database = new Database();
	$db = $database->connect();

	// Instantiate question object
	$question = new Question($db);

	// Qquestion query
	$result = $question->read();
	// Get row count
	$num = $result->rowCount();

	// Check for questions
	if($num > 0) {
		$questions_arr = array();
		$questions_arr['data'] = array();

		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			extract($row); // This allows us to access fields directly ($id) rather than via row ($row['id'])

			if($id == 331) {
				error_log(htmlspecialchars_decode($question_text));
			}
			$question_item = array(
				'id' 			=> $id,
				'question_text' => $question_text,
				'answer' 		=> $answer,
				'correct' 		=> $correct,
				// 'category_id'	=> $category_id,
				// 'type' 			=> $type,
				// 'difficulty'	=> $difficulty
			);

			// Push to data
			array_push($questions_arr['data'], $question_item);
		}
		// Output as JSON
		echo json_encode($questions_arr);
	} else {
		// No questions
		echo json_encode(
			array('message'=>"No questions found")
		);
	}
