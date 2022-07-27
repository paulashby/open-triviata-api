<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../utilities/RateLimiter/SlidingWindow.php";

// Initialise rate limiter
$ip = $_SERVER['REMOTE_ADDR'];
define('REQUESTS_PER_MINUTE', 100);
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
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
		reset_token($query_params);
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