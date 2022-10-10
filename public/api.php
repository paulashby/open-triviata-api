<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../models/Question.php";
include_once "../utilities/Validator.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

// https://www.php.net/manual/en/function.filter-var.php
// https://www.php.net/manual/en/filter.filters.validate.php
$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

// https://stackoverflow.com/questions/42700310/how-to-reference-to-a-folder-that-is-above-document-root-in-php
$apiconfig = parse_ini_file(realpath(__DIR__ . "/../") . "/apiconfig.ini");
$max_questions = $apiconfig['max_questions'];

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow($apiconfig['req_per_minute'], $apiconfig['limit_all']);
$limiter->limit($ip);

// Validate user input - amount, category and token are permitted only if numeric/alphanumeric; all other parameters are checked against permitted values
$validator = new Validator($_SERVER['REQUEST_URI'], $max_questions);
$request_breakdown = $validator->validate();

// Instantiate database and connect
$database = new Database($apiconfig);
$db = $database->connect();

include_once "../utilities/Encoder.php";
$encoder = new Encoder();

$token = false;
if (isset($request_breakdown['token'])) {
	include_once "../utilities/Token.php";
	$token = new Token($request_breakdown['token']);
}

$question = new Question($db, $token);

$by_id = array_key_exists('ids', $request_breakdown);

// Question query
$result = $question->read($request_breakdown, $by_id);

// Get row count
$num = $result->rowCount();

if ($token && $num === 0 ) {
	token_empty();
}

$retrieved = array();
$question_arr = array();
$question_arr['results'] = array();
$question_item = array(
	'id' => 0
);

while($row = $result->fetch(PDO::FETCH_ASSOC)) {

	// Processing row data @ 26:14
	// https://www.youtube.com/watch?v=OEWXbpUMODk

	extract($row); // This allows us to access fields directly ($id) rather than via row ($row['id'])

	if($id !== $question_item['id']) {
		// New question or first in list

		// Keep record of retrieved question ids for current token so we can ensure unique results
		$retrieved[] = $id;
		
		if(count($question_item) > 1) {
			// This is a new question - push to results and start new
			if ($request_breakdown['encode'] !== "none") {
				$question_item = $encoder->encodeItem($question_item, $request_breakdown['encode']);				
			}
			// Push to results
			array_push($question_arr['results'], $question_item); 
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
	if($correct) {
		$question_item['correct_answer'] = $answer;
	} else {
		$question_item['incorrect_answers'][] = $answer;
	}
}
// Process final item as this isn't handled in while loop
if ($request_breakdown['encode'] !== "none") {
	$question_item = $encoder->encodeItem($question_item, $request_breakdown['encode']);
}
// Push final item to results
array_push($question_arr['results'], $question_item);

if ($by_id) {
	// Make sure we've retrieved all the requested questions
	$unavailable = array_diff($request_breakdown['ids'], $retrieved);

	if (count($unavailable)) {
		// Return ids of unavailable questions with error code
		die(json_encode(array(
			'response_code' => 5,
			'results' => array(
				'unavailable' => $unavailable
			)
		)));
	}
}

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
	die(json_encode(array(
		'response_code' => 1,
		'results' => array()
	)));
}

// Output as JSON
echo json_encode($question_arr);

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
