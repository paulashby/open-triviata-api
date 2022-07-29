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
define('MAX_QUESTIONS', 50);

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
$limiter->limit($ip);

// Validate and sanitise user input
$request = new Request($_SERVER['REQUEST_URI']);
$request_breakdown = $request->breakdown();

// Use MAX_QUESTIONS as fallback for amount
$request_breakdown['amount'] = $request_breakdown['amount'] ?? MAX_QUESTIONS;

// Instantiate database and connect
$database = new Database();
$db = $database->connect();

if (isset($request_breakdown['encode'])) {
	include_once "../utilities/Encoder.php";
	$encoder = new Encoder();
}

$token = false;

if (isset($request_breakdown['token'])) {
	include_once "../utilities/Token.php";
	$token = new Token($request_breakdown['token']);
}

$question = new Question($db, MAX_QUESTIONS, $token);

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
			// This is a new question - current question_item is complete. Push to results and start new
			array_push($questions_arr['results'], $question_item); 
		}

		if (isset($request_breakdown['encode'])) {
			$question_text = $encoder->encode($question_text, $request_breakdown['encode']);
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
// Push final item to results as this isn't pushed in while loop
array_push($questions_arr['results'], $question_item);

// Can't just check num rows as each question has multiple, so we either check this here after assembling the questions or do a separate DB call to check
if ($token) {
	if (count($retrieved) !== $request_breakdown['amount']) {
		token_empty();
	}	
	// Write question ids to token
	$token->update($retrieved);
}

// Output as JSON
echo json_encode($questions_arr);

/**
 * Provide Empty Token response
 *
 * @return Array containing empty token response code and empty results array
 */ 
function token_empty() {
	// Query returned incorrect number of questions
	die(json_encode(array(
		'response_code' => 4,
		'results' => array()
	)));
}
