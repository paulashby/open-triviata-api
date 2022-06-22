<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../models/question.php";

// Instantiate database and connect
$database = new Database();
$db = $database->connect();

// Instantiate question object
$question = new Question($db);

// Question query
$result = $question->read();
// Get row count
$num = $result->rowCount();

// Check for questions
if($num > 0) {
	$questions_arr = array();
	$questions_arr['results'] = array();
	$question_item = array(
		'id' => 0
	);

	while($row = $result->fetch(PDO::FETCH_ASSOC)) {

		extract($row); // This allows us to access fields directly ($id) rather than via row ($row['id'])

		if($id !== $question_item['id']) {
			
			// New question or first in list

			if(count($question_item) > 1) {
				// This is a new question - current question_item is complete. Push to results and start new
				array_push($questions_arr['results'], $question_item); 
			}

			$question_item = $question_item = array(
				'category' 			=> $category,
				'type' 				=> $type,
				'difficulty'		=> $difficulty,
				'question' 			=> $question_text,
				'id' 				=> $id,
				'correct_answer'	=> "",
				'incorrect_answers' => array()
			);
		}

		if($type == "boolean") {
			$question_item['correct_answer'] = $correct ? "True" : "False";
			$question_item['incorrect_answers'][] = $correct ? "False" : "True";

		} else if($correct) {
			// Multiple choice
			$question_item['correct_answer'] = $answer;
		} else {
			$question_item["incorrect_answers"][] = $answer;
		}
	}
	// Push final item to results as this isn't pushed in while loop
	array_push($questions_arr['results'], $question_item);

	// Output as JSON
	echo json_encode($questions_arr);
} else {
	// No questions
	echo json_encode(
		array('message'=>"No questions found")
	);
}
