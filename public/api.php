<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../models/Question.php";
include_once "../utilities/Validator.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

define('REQUESTS_PER_MINUTE', 100);
define('MAX_QUESTIONS', 50);

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
$limiter->limit($ip);

// Validate user input - amount, category and token are permitted only if numeric/alphanumeric; all other parameters are checked against permitted values
$validator = new Validator($_SERVER['REQUEST_URI'], MAX_QUESTIONS);
$request_breakdown = $validator->validate();

// Instantiate database and connect
$database = new Database();
$db = $database->connect();

include_once "../utilities/Encoder.php";
$encoder = new Encoder();

$token = false;
if (isset($request_breakdown['token'])) {
	include_once "../utilities/Token.php";
	$token = new Token($request_breakdown['token']);
}

$question = new Question($db, $token);

// Question query
$result = $question->read($request_breakdown);

// Get row count
$num = $result->rowCount();

if ($token && $num === 0 ) {
	token_empty();
}

$retrieved = array();
$questions_arr = array();
$questions_arr['results'] = array();
$question_item = array(
	'id' => 0
);

while($row = $result->fetch(PDO::FETCH_ASSOC)) {

	extract($row); // This allows us to access fields directly ($id) rather than via row ($row['id'])

	if($id !== $question_item['id']) {

		// Keep record of retrieved question ids for current token so we can ensure unique results
		$retrieved[] = $id;
		
		// New question or first in list
		if(count($question_item) > 1) {
			// This is a new question - push to results and start new
			$question_item = $encoder->encodeItem($question_item, $request_breakdown['encode']);
			// Push to results
			array_push($questions_arr['results'], $question_item); 
		}
		$question_item = array(
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
// Process final item as this isn't handled in while loop
$question_item = $encoder->encodeItem($question_item, $request_breakdown['encode']);
// Push final item to results
array_push($questions_arr['results'], $question_item);

// Can't just check num rows as each question has multiple, so we either check this here after assembling the questions or do a separate DB call to check
$below_quota = isset($request_breakdown['amount']) && count($retrieved) !== (int)$request_breakdown['amount'];

if ($token) {
	if ($below_quota) {
		token_empty();
	}	
	// Write question ids to token
	$token->update($retrieved);
} else if ($below_quota) {
	// Query returned too few questions - no token, so this is a code 1
	error_log("CODE 1\n");
	die(json_encode(array(
		'response_code' => 1,
		'results' => array()
	)));
}

// Output as JSON
echo json_encode($questions_arr);

/**
 * Provide Empty Token response
 *
 * @return Array containing empty token response code and empty results array
 */ 
function token_empty() {
	// Query returned too few questions
	die(json_encode(array(
		'response_code' => 4,
		'results' => array()
	)));
}
