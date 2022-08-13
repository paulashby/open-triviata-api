<?php

include_once "../config/Database.php";
include_once "../utilities/RateLimiter/SlidingWindow.php";

// Start session for CSRF tokens
session_start();

$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

define('REQUESTS_PER_MINUTE', 100);

// Limit requests
$limiter = new SlidingWindow(REQUESTS_PER_MINUTE);
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

	if (isset($_POST['ids'])) {

		$ids = $_POST['ids'];

		if (strlen($ids)) {
			$url .= $delimiter . "ids=" . filter_var($ids, FILTER_SANITIZE_STRING);
			$delimiter = "&";
		}		

		if ($_POST['encode'] !== "default") {
			$url .= $delimiter . "encode=" . filter_var($_POST['encode'], FILTER_SANITIZE_STRING);
		}
	} else {
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
	// Generate token for CSRF protection
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
}


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

	<meta name="theme-color" content="#87c5f1">
	
	<!-- Bootstrap Core CSS -->
	<link rel="stylesheet" href="https://opentdb.com/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="/css/triviata.css" type="text/css">
	
	<!-- Custom Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet" type="text/css">
	
	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

	<script src="scripts/otriviata.js"></script>

	<script>
	   // Prevent Firefox flash of unstyled content
	   let FF_FOUC_FIX;
	 </script>
	
</head>
<body>	
	<div class='container'>
		<img src='/img/open-triviata-logo.svg' alt='Open Triviata logo' width='120' id='logo'>
		<?php
			if (isset($url)) {
				echo "<div class='alert alert-success'>
					<strong>API URL Generated!: </strong><input type='text' class='form-control' value=$url readonly=''>
				</div>";
			}
		?>
		<h1>Triviata API</h1>	
		<p>The Open Triviata Database provides a completely free JSON API for use in programming projects. All questions have been sourced from <a href='https://opentdb.com/'>The Open Trivia Database</a>, with the notable difference that the Open Triviata Database provides an ID number with each question. These numbers are not related to those displayed when viewing the Open Triviata Database in a web browser (which are not available via their API). If you don&apos;t need the ID numbers, you might as well use the original Database :)</p>
		<p>All data provided by the API is available under the Creative Commons Attribution-ShareAlike 4.0 International License. <a href='https://creativecommons.org/licenses/by-sa/4.0/'><img src='https://licensebuttons.net/l/by-sa/4.0/80x15.png'></a></p>
		<section>
			<button class='btn btn-xs btn-primary btn-block' id='btn-doc' role='button' data-toggle='collapse' data-parent='#apiInfo' href='#apiInfo' aria-expanded='false' aria-controls='apiInfo'>API Documentation</button>
			<section class='collapse' id='apiInfo'>
				<section class='well'>
							<h3>Getting Started</h3>
							<p>
								To get started using the Open Triviata Database API, use this URL: <input type='text' class='form-control' value='<?=$base_url?>api.php?amount=10' readonly=''>
								For more settings or help using the API, read along below. Alternatively, you can use the helper form to craft your specific query.
							</p>							
							<p>
								To retrieve one or more questions by ID number, simply use the 'ids' parameter with a comma-separated list (NOTE: all other parameters will be discarded with the exception of encode - see below): <input type='text' class='form-control' value='<?=$base_url?>api.php?ids=1,500,1000' readonly=''>
							</p>
							<h3>Session Tokens</h3>
							<p>
							 	Session Tokens are unique keys that will help keep track of the questions the API has already retrieved. By appending a Session Token to a API Call, the API will never give you the same
							 	question twice. Over the lifespan of a Session Token, there will eventually reach a point where you have exhausted all the possible questions in the database. At this point, the API will
							 	respond with the approperate &lsquo;Response Code&rsquo;. From here, you can either &lsquo;Reset&rsquo; the Token, which will wipe all past memory, or you can ask for a new one.
							 	<br>
							 	<br>
							 	<i><b>Session Tokens will be deleted after 6 hours of inactivity.</b></i>
						 	</p>
						 	<p>
								Using a Session Token: <input type='text' class='form-control' value='<?=$base_url?>api.php?amount=10&amp;token=YOURTOKENHERE' readonly=''>
						 	</p>
						 	<p>
								Retrieve a Session Token: <input type='text' class='form-control' value='<?=$base_url?>api_token.php?command=request' readonly=''>
						 	</p>
						 	<p>
								Reset a Session Token:  <input type='text' class='form-control' value='<?=$base_url?>api_token.php?command=reset&amp;token=YOURTOKENHERE' readonly=''>
						 	</p>

				 			<h3>Response Codes</h3>
							<p>
								The API appends a &lsquo;Response Code&rsquo; to each API Call to help tell developers what the API is doing.
							</p>
							<ul>
								<li> Code 0: <b>Success</b>           Returned results successfully.</li>
								<li> Code 1: <b>No Results</b>        Could not return results. The API doesn&apos;t have enough questions for your query. (Ex. Asking for 50 Questions in a Category that only has 20.)</li>
								<li> Code 2: <b>Invalid Parameter</b> Contains an invalid parameter. Arguements passed in aren&apos;t valid. (Ex. Amount = Five)</li>
								<li> Code 3: <b>Token Not Found</b>   Session Token does not exist.</li>
								<li> Code 4: <b>Token Empty</b>       Session Token has returned all possible questions for the specified query. Resetting the Token is necessary.</li>
							</ul>

							<h3>Encoding Types</h3>
							<p>
								The Open Triviata Database may contain questions which contain Unicode or Special Characters. For this reason, the API returns results in a encoded format. You can specify the desired encoding format
								using the examples below. If the encode type is not present in the URL, it will follow the default encoding.
							</p>
							<p>
								API Call with Encode Type (urlLegacy, url3986, base64):<input type='text' class='form-control' value='<?=$base_url?>api.php?amount=10&amp;encode=url3986' readonly=''>
							</p>

							Example Sentence (Non Encoded): &lsquo;Don't forget that π = 3.14 &amp; doesn't equal 3.&rsquo;
							<ul>
								<li><b>Default Encoding (HTML Codes):</b> <input type='text' class='form-control' value='Don&amp;‌#039;t forget that &amp;‌pi; = 3.14 &amp;‌amp; doesn&amp;‌#039;t equal 3.' readonly=''></li>
								<li><b>Legacy URL Encoding:</b> <input type='text' class='form-control' value='Don%27t+forget+that+%CF%80+%3D+3.14+%26+doesn%27t+equal+3.' readonly=''></li>
								<li><b>URL Encoding (<a href='https://www.ietf.org/rfc/rfc3986.txt'>RFC 3986</a>):</b> <input type='text' class='form-control' value='Don%27t%20forget%20that%20%CF%80%20%3D%203.14%20%26%20doesn%27t%20equal%203.' readonly=''></li>
								<li><b>Base64 Encoding:</b> <input type='text' class='form-control' value='RG9uJ3QgZm9yZ2V0IHRoYXQgz4AgPSAzLjE0ICYgZG9lc24ndCBlcXVhbCAzLg==' readonly=''></li>
							</ul>

							<h3>Helper API Tools</h3>
							<p>
								There are some functions in the API which can be useful to developers.
							</p>
							<p>
								<b>Category Lookup:</b> Returns the entire list of categories and ids in the database.
								<input type='text' class='form-control' value='<?=$base_url?>api_category.php' readonly=''>
							</p>
							<p>
								<b>Category Question Count Lookup:</b> Returns the number of questions in the database, in a specific category.
								<input type='text' class='form-control' value='<?=$base_url?>api_count.php?category=CATEGORY_ID_HERE' readonly=''>
							</p>
							<p>
								<b>Global Question Count Lookup:</b> Returns the number of ALL questions in the database. Note: while <a href='https://opentdb.com/'>The Open Trivia Database</a> provides additional counts for Pending, Verified, and Rejected questions, The Open Triviata Database provides only verified questions.
								<input type='text' class='form-control' value='<?=$base_url?>api_count_global.php' readonly=''>
							</p>

							<h3>Limitations</h3>
							<ul>
				 				<li>Only 1 Category can be requested per API Call. To get questions from any category, don&apos;t specify a category.</li>
				 				<li>A Maximum of 50 Questions can be retrieved per call.</li>
				 				<li>Maximum of <?=REQUESTS_PER_MINUTE?> requests per minute.</li>
				 			</ul>
				 	</section>
			</section>
			<form  method='post' class='form-api' id='param-form'>
				<h3 class='form-signin-heading'>API Helper</h3>
				<p>Use this form to build API requests. Alternatively, you can <a role='button' tabindex='0' id='id-form-button'>build a request using a list of question ID numbers</a>.</p>
				<label for='amount'>Number of Questions:</label>
				<input type='number' id='amount' class='form-control' name='amount' value='30' placeholder='Number of questions required' min='0' max='50'>

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
						<select name='category' id='category' class='form-control'>
						<option value='any'>Any Category</option>";

						while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

							extract($row); 
							echo "<option value='$id'>$name</option>";
						}
						echo "</select>";
						echo ob_get_clean();
					}
				?>
				<label for='difficulty'>Select Difficulty:</label>
				<select name='difficulty' id='difficulty' class='form-control'>
					<option value='any'>Any Difficulty</option>
					<option value='easy'>Easy</option>
					<option value='medium'>Medium</option>
					<option value='hard'>Hard</option>
				</select>
				<label for='type'>Select Type:</label>
				<select name='type' id='type' class='form-control'>
					<option value='any'>Any Type</option>
					<option value='multiple'>Multiple Choice</option>
					<option value='boolean'>True / False</option>
				</select>
				<label for='encode'>Select Encoding:</label>
				<select name='encode' id='encode' class='form-control'>
					<option value='default'>Default Encoding</option>
					<option value='urlLegacy'>Legacy URL Encoding</option>
					<option value='url3986'>URL Encoding (RFC 3986)</option>
					<option value='base64'>Base64 Encoding</option>
				</select>
				<input type='hidden' name='token' value='<?php echo $_SESSION["token"] ?? "" ?>'>
				<br>
				<input type='submit' value='Generate API URL' id='btn-gen-url' class='btn btn-lg btn-primary btn-block'/>
			</form>
			<form  method='post' class='form-api' id='id-form'>
				<h3 class='form-signin-heading'>API Helper - by question ID</h3>
				<p>Use this form to build API requests using a list of question ID numbers. Alternatively, you can <a role='button' tabindex='1' id='param-form-button'>build a request by selecting from the available parameters</a>.</p>
				<label for='ids'>Question ID Numbers:</label>
					<input type='text' id='ids' class='form-control' name='ids' pattern='^\d+(,\d+)*$' placeholder='Comma-separated id numbers'>
				<label for='encode'>Select Encoding:</label>
				<select name='encode' id='encode' class='form-control'>
					<option value='default'>Default Encoding</option>
					<option value='urlLegacy'>Legacy URL Encoding</option>
					<option value='url3986'>URL Encoding (RFC 3986)</option>
					<option value='base64'>Base64 Encoding</option>
				</select>
				<input type='hidden' name='token' value='<?php echo $_SESSION["token"] ?? "" ?>'>
				<br>
				<input type='submit' value='Generate API URL' id='btn-gen-url' class='btn btn-lg btn-primary btn-block'/>
			</form>
		</section>
	</div>
</body>
</html>