<?php

// The content for this page is largely lifted from the Open Trivia Databse API documentation page at https://opentdb.com/api_config.php. The HTML is my own, making use of the UIKit css framework
include_once "../config/Database.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

// Start session for CSRF tokens
// https://www.phptutorial.net/php-tutorial/php-csrf/
session_start();

$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$apiconfig = parse_ini_file(realpath(__DIR__ . "/../") . "/apiconfig.ini");
$max_questions = $apiconfig['max_questions'];

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow($apiconfig['req_per_minute'], $apiconfig['limit_all']);
$limiter->limit($ip);

$scheme = $_SERVER['REQUEST_SCHEME'];
$host = $_SERVER['HTTP_HOST'];
$base_url = "$scheme://$host/";

if($_SERVER['REQUEST_METHOD'] == "POST") {

	$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

	if (!$token || $token !== $_SESSION['token']) {
	    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
	    die();
	}

	
	$url = $base_url;
	$delimiter = "api.php?";

	// Url tile is shown when modifier is empty string
	$url_tile_modifier = "";

	if (isset($_POST['ids'])) {
		// Build url to retrieve questions by id
		$ids = $_POST['ids'];

		if (strlen($ids)) {
			// Id numbers have been provided
			$url .= $delimiter . "ids=" . filter_var($ids, FILTER_SANITIZE_STRING);
			$delimiter = "&";
		}		

		if ($_POST['encode'] !== "default") {
			// Include encoding scheme
			$url .= $delimiter . "encode=" . filter_var($_POST['encode'], FILTER_SANITIZE_STRING);
		}
	} else {
		// Build request url with parameters
		foreach ($_POST as $param_name => $param_val) {

			$param_val = filter_var($param_val, FILTER_SANITIZE_STRING);

			if ($param_val !== "any" && $param_val !== "default"  && $param_name !== "token") {
				$url .= $delimiter;
				$url .= "$param_name=$param_val";
				$delimiter = "&";
			}
		}
	}
} else {
	$url = "";
	// Hide url tile
	$url_tile_modifier = " ot-url-tile--hide";
	$assembled_request = "";
	// Generate token for CSRF protection
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
}
// Token for forms
$form_token = $_SESSION['token'] ?? "";
// Instantiate database and connect
$database = new Database($apiconfig);
$conn = $database->connect();

// Get category data and render select menu accordingly
$query = "SELECT id, category AS name FROM categories ORDER BY id;";
// Run the query
$stmt = $conn->prepare($query);
$stmt->execute();

// Get row count
$num = $stmt->rowCount();
// Set up category select options
$category_options = "<option value='any'>Any Category</option>";

if ($num !== 0 ) {
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		extract($row); 
		$category_options .= "<option value='$id'>$name</option>";
	}
}
include_once "../templates/index_template.php";
?>