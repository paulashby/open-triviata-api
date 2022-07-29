<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

$ip = $_SERVER['REMOTE_ADDR'];

define('REQUESTS_PER_MINUTE', 100);

// Limit requests
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
$limiter->limit($ip);

// Instantiate database and connect
$database = new Database();
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
