<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../models/question.php";
include_once "../utilities/Request.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

$ip = $_SERVER['REMOTE_ADDR'];
// Seems like this is most reliable method, BUT read this for security concerns (fine I think as I'm not using it to grant access to anything private)
// https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php

define('REQUESTS_PER_MINUTE', 100);

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
$limiter->limit($ip);

// Validate and sanitise user input
$request = new Request($_SERVER['REQUEST_URI']);
$request_breakdown = $request->breakdown();

if (isset($request_breakdown['encode'])) {
	include_once "../utilities/Encoder.php";
	$encoder = new Encoder();
}
/*
if (isset($request_breakdown['token'])) {
	// This is a token request which is a diff endpoint
	include_once "../utilities/Token.php";
	$token = new Token();
	$retrieved = $token->retrieved($request_breakdown['token']);
}
*/

// Instantiate database and connect
$database = new Database();
$db = $database->connect();

// Instantiate question object
$question = new Question($db);

// Question query
$result = $question->read($request_breakdown);
// Get row count
$num = $result->rowCount();

// Check that num == amount requested

/*

	Tokens
	------
	Trickier than might first appear. Need to -
	• Generate token
	• Store tokens with list of previously-served questions
	• Exclude previously-served questions from further responses
	• Reset token - keep it, but wipe out records of previously-served questions

*/

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

			if (isset($request_breakdown['encode'])) {
				$question_text = $encoder->encode($question_text, $request_breakdown['encode']);
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

	// $questions_arr['ip'] = $ip; // Debug rate limiter

	// Output as JSON
	echo json_encode($questions_arr);
} else {
	// No questions
	echo json_encode(
		array('message'=>"No questions found")
	);
}
