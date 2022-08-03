<?php

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Open Triviata Database</title>
</head>
<body>
	<h1>Triviata API</h1>	
	<p>The Open Triviata Database provides a completely free JSON API for use in programming projects. All questions have been sourced from <a href='https://opentdb.com/'>The Open Trivia Database</a>, with the notable difference that the Open Triviata Database provides an ID number with each question. These numbers are not related to those displayed when viewing the Open Trivia Database via a web browser (which are not available via their API).</p>
	<p>All data provided by the API is available under the Creative Commons Attribution-ShareAlike 4.0 International License</p>
	<section>
		<h2>API Documentation</h2>
		<form>
			<label for='amount'>Number of Questions:</label>
			<input type='number' id='amount' name='amount' min='1' max='50' value='10'>

			<?php 
				// Get category data and render select menu accordingly
				$query = "SELECT id, category AS name FROM categories ORDER BY id;";
				// Run the query
				$stmt = $conn->prepare($query);
				$stmt->execute();

				// Get row count
				$num = $stmt->rowCount();

				if ($num !== 0 ) {

					ob_start();
					echo "<label for='category'>Select Category:</label>
					<select name='category' id='category'>
					<option value='$any'>Any Category</option>";

					while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

						extract($row); 
						echo "<option value='$id'>$name</option>";
					}
					echo "</select>";

					echo ob_get_clean();
				}

				// Get difficulty & type data and render select menu accordingly
				$query = "SELECT DISTINCT difficulty, type FROM questions;";
				// Run the query
				$stmt = $conn->prepare($query);
				$stmt->execute();

				// Get row count
				$num = $stmt->rowCount();

				if ($num !== 0 ) {
					$difficulty_unique = array();
					$type_unique = array();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

						extract($row); 						
						// Make sure we only use unique values
						if (!in_array($difficulty, $difficulty_unique)) {
							$difficulty_unique[] = $difficulty;
						}
						if (!in_array($type, $type_unique)) {
							$type_unique[] = $type;
						}
					}
					ob_start();
					echo "<label for='difficulty'>Select Difficulty:</label>
					<select name='difficulty' id='difficulty'>
					<option value='$any'>Any Difficulty</option>";

					foreach ($difficulty_unique as $difficulty) {
						// Add options to select menu from unique difficulty levels array
						echo "<option value='$difficulty'>" . ucwords($difficulty) . "</option>";
					}
					echo "</select>";

					echo "<label for='type'>Select Type:</label>
					<select name='type' id='type'>
					<option value='$any'>Any Type</option>";

					foreach ($type_unique as $type) {
						// Add options to select menu from unique types array
						echo "<option value='$type'>"; 
						if ($type === "multiple") {
							// Matc wording used for  multiple choice on Open Trivia API page
							$type = "Multiple Choice";
						} else if ($type === "boolean") {
							// Match wording used for boolean on Open Trivia API page
							$type = "True / False";
						}
						echo $type;
						echo "</option>";
					}
					echo "</select>";

					echo ob_get_clean();
				}
			?>	
			<input type='submit' value='Generate API URL'/>
		</form>
	</section>
</body>
</html>