<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$apiconfig = parse_ini_file(realpath(__DIR__ . "/../") . "/apiconfig.ini");
$max_questions = $apiconfig['max_questions'];

// Limiter will prevent data loading and add appropriate headers if rate limit is exceeded (NOTE in case of suspected DDOS, set optional second arg to true - limits every user over 5 minute window)
$limiter = new SlidingWindow($apiconfig['req_per_minute'], $apiconfig['limit_all']);
$limiter->limit($ip);

// Instantiate database and connect
$database = new Database($apiconfig);
$conn = $database->connect();

$query = "SELECT id, category AS name FROM categories ORDER BY id;";

// Run the query
$stmt = $conn->prepare($query);
$stmt->execute();

// Get row count
$num = $stmt->rowCount();

if ($num === 0 ) {
	die(json_encode(array(
		'Error' => "Unable to retrieve category data",
		'results' => array()
	)));
}

$category_arr = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

	extract($row); 

	$category_arr[] = array(
		'id'	=> (int)$id,
		'name'	=> $name
	);
}

echo (json_encode(array(
	'trivia_categories' => $category_arr
)));
