<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once "../config/Database.php";
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

// Instantiate database and connect
$database = new Database($apiconfig);
$conn = $database->connect();

// https://stackoverflow.com/questions/4730798/what-is-the-difference-between-serverrequest-uri-and-getq
// https://stackoverflow.com/questions/11480763/how-can-i-get-parameters-from-a-url-string/11480852#11480852
$parts = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($parts, $query_params);

if (!array_key_exists('category', $query_params) || !is_numeric($query_params['category'])) {
	die(json_encode(array(
		'response_code' => 2,
		'results' => array()
	)));
}
$category = $query_params['category'];

$query = "SELECT COUNT(q.id) AS count, q.difficulty 
FROM questions AS q
INNER JOIN categories ON q.category = categories.id
WHERE q.category = $category
GROUP BY q.difficulty;";

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
    'category_id' => (int)$category,
    'category_question_count'				=> array(
            'total_question_count'			=> 0,
            'total_easy_question_count'		=> 0,
            'total_medium_question_count'	=> 0,
            'total_hard_question_count'		=> 0
        )
);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

	// Basics of accessing row data @ 26:14
	// https://www.youtube.com/watch?v=OEWXbpUMODk

	extract($row); 

	$category_arr['category_question_count']['total_question_count'] += (int)$count;
	$category_arr['category_question_count']["total_{$difficulty}_question_count"] = (int)$count;
}

echo json_encode($category_arr);
