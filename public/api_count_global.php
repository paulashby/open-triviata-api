<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

define('REQUESTS_PER_MINUTE', 100);

// Limit requests
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
$limiter->limit($ip);

// Instantiate database and connect
$database = new Database();
$conn = $database->connect();

$query = "SELECT q.category, COUNT(id) AS count 
FROM questions AS q 
GROUP BY q.category 
ORDER BY q.category ASC;";

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

$category_arr = array(
	'overall_num_of_verified_questions' => 0
);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

	extract($row); 

	$category_arr['overall_num_of_verified_questions'] += (int)$count;
	$category_arr['categories_num_of_verified_questions'][$category] = (int)$count;
}

echo json_encode($category_arr);
