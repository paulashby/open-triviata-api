<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../utilities/RateLimiter/SlidingWindow.php";

// Initialise rate limiter
$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$apiconfig = parse_ini_file(realpath(__DIR__ . "/../") . "/apiconfig.ini");
$max_questions = $apiconfig['max_questions'];

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow($apiconfig['req_per_minute'], $apiconfig['limit_all']);
$limiter->limit($ip);

// Parse request parameters
$parts = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($parts, $query_params);

if (array_key_exists('command', $query_params)) {
	
	switch ($query_params['command']) {
		case 'request':
		// get a new token
		include_once "../utilities/Token.php";
		$token = new Token();
		$response = array(
			'response_code' 	=> 0,
			'response_message' 	=> "Token Generated Successfully!",
			'token' 			=> $token->tokenName()
		);
		echo json_encode($response);
		break;

		case 'reset':
		// Response code 3 for fail, 0 for success
		$response_code = reset_token($query_params) === false ? 3 : 0;
		$response = array(
			'response_code' 	=> $response_code,
			'token' 			=> $query_params['token']
		);
		echo json_encode($response);
		break;
		
		default:
		echo invalidParameter();
	}
}

/**
 * Clear data from given token
 */ 
function reset_token($query_params) {

	if (array_key_exists('token', $query_params)) {
		// Remove all data from file, leave file in place
		include_once "../utilities/Token.php";
		$token = new Token($query_params['token']);
		$token->reset();
	} else {
		die(invalidParameter());
	}
}

/**
 * Provide invalid parameter response
 *
 * @return Array containing invalid parameter response code and empty results array
 */ 
function invalidParameter() {
	return json_encode(array(
		'response_code' => 2,
		'results' => array()
	));
}