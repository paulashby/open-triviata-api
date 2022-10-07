<!DOCTYPE html>
<html class="ot-bg uk-light" lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Open Triviata Database</title>
		
		<!-- jQuery -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

		<!-- UIKit CSS -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/css/uikit.min.css" />

		<!-- UIkit JS -->
		<script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit-icons.min.js"></script>

		<script src="scripts/otriviata.js"></script>
		<link rel="stylesheet" href="/css/otriviata.css" type="text/css">
	</head>
	<body>
		<div class="uk-container uk-container-xsmall">
			<img src="/img/open-triviata-logo.svg" alt="Open Triviata logo" width="200" id="logo">
			<div class="uk-tile uk-tile-primary uk-padding-small uk-border-rounded uk-animation-scale-up ot-url-tile<?php echo $url_tile_modifier; ?>">
				<div id='ot-clipboard' class="uk-float-right">
					<p class="uk-animation-fade ot-copy-status ot-copy-status--success uk-icon=" uk-icon="icon: check"></p>
					<p class="uk-animation-fade ot-copy-status ot-copy-status--fail uk-icon=" uk-icon="icon: close"></p>
					<a role="button" id="copy-button" class="ot-icon-copy" uk-icon="icon: copy"></a>
				</div>
				<label for="generated-url" class="uk-form-label ot-generated-url-label uk-float-left">API URL:</label>
				<div class="uk-form-controls" style="width: 100%;">
					<input type="text" id="generated-url" class="uk-input ot-doc-eg uk-margin-small-bottom uk-margin-small-top" value=" <?php echo $url; ?>" readonly="">
				</div>
			</div>
			<h1>Open Triviata API</h1>
			<p>The Open Triviata Database provides a completely free JSON API for use in programming projects, providing questions sourced from <a href='https://opentdb.com/'>The Open Trivia Database</a>. If you don&apos;t need question ID numbers or unencoded data, the original Open Trivia Database will serve you perfectly well :)</p>
			<p>All data provided by the API is available under the Creative Commons Attribution-ShareAlike 4.0 International License. <a href="https://creativecommons.org/licenses/by-sa/4.0/"><img src="https://licensebuttons.net/l/by-sa/4.0/80x15.png" alt="Creative Commons logo"></a></p>
			<ul uk-accordion class="ot-doc">
				<li>
					<a class="uk-accordion-title ot-doc-title" href="#">API Documentation</a>
					<div class="uk-accordion-content ot-doc-content">
						<h3>Getting Started</h3>
						<p>To get started using the Open Triviata Database API, use this URL: </p>
						<div class="uk-form-controls">
							<input type="text" class="uk-input ot-doc-eg" value="<?=$base_url?>api.php?amount=10" readonly="">
						</div>
						<p>For more settings or help using the API, read along below. Alternatively, you can use the &lsquo;Build a Request&rsquo; form to craft your specific query.</p>
						<p>To retrieve one or more questions by ID number, simply use the 'ids' parameter with a comma-separated list (NOTE: all other parameters will be discarded with the exception of encode):</p>
						<div class="uk-form-controls">
							<input type="text" class="uk-input ot-doc-eg" value="<?=$base_url?>api.php?ids=1,500,1000" readonly="">
						</div>
						<h3>Session Tokens</h3>
						<p>Session Tokens are unique keys that will help keep track of the questions the API has already retrieved. By appending a Session Token to a API Call, 
							the API will never give you the same question twice. Over the lifespan of a Session Token, there will eventually reach a point where you have exhausted 
							all the possible questions in the database. At this point, the API will respond with the approperate &lsquo;Response Code&rsquo;. From here, you can either 
						&lsquo;Reset&rsquo; the Token, which will wipe all past memory, or you can ask for a new one.</p>
						<p><i><b>Session Tokens will be deleted after 6 hours of inactivity.</b></i></p>
						<label for="using-st" class="uk-form-label ot-doc-label">Use a session token:</label>
						<div class="uk-form-controls">
							<input type="text" id="using-st" class="uk-input ot-doc-eg" value="<?=$base_url?>api.php?amount=10&amp;token=YOURTOKENHERE" readonly="">
						</div>
						<label for="retrieve-st" class="uk-form-label ot-doc-label">Retrieve a Session Token:</label>
						<div class="uk-form-controls">
							<input type="text" id="retrieve-st" class="uk-input ot-doc-eg" value="<?=$base_url?>api_token.php?command=request" readonly="">
						</div>
						<label for="reset-st" class="uk-form-label ot-doc-label">Reset a Session Token:</label>
						<div class="uk-form-controls">
							<input type="text" id="reset-st" class="uk-input ot-doc-eg" value="<?=$base_url?>api_token.php?command=reset&amp;token=YOURTOKENHERE" readonly="">
						</div>
						<h3>Response Codes</h3>
						<table class="ot-code-table">
							<tr>
								<th class="ot-col-code">Code</th>
								<th class="ot-col-status">Status</th>
							</tr>
							<tr>
								<td class="ot-code-num">0</td>
								<td><b>Success</b> Results successfully returned.</td>
							</tr>
							<tr>
								<td class="ot-code-num">1</td>
								<td><b>No Results</b> The API doesn&apos;t have enough questions to satisfy query. (Eg requesting 50 Questions in a Category that only has 20.)</td>
							</tr>
							<tr>
								<td class="ot-code-num">2</td>
								<td><b>Invalid Parameter</b> One or more arguments aren&apos;t valid. (Eg amount=Five)</td>
							</tr>
							<tr>
								<td class="ot-code-num">3</td>
								<td><b>Token Not Found</b> Session Token does not exist.</td>
							</tr>
							<tr>
								<td class="ot-code-num">4</td>
								<td><b>Token Empty</b> Session Token has returned all possible questions for given query. Token should be reset.</td>
							</tr>
							<tr>
								<td class="ot-code-num">5</td>
								<td><b>ID Not Found</b> Question ID does not exist. Response includes array of offending IDs.</td>
							</tr>
						</table>
						<h3>Encoding Types</h3>
						<p>The Open Triviata Database may contain questions which contain Unicode or Special Characters. For this reason, the API returns results in a encoded format. You can specify the desired encoding format using the examples below. If the encode type is not present in the URL, it will follow the default encoding. To disable encoding use the value &lsquo;none&rsquo;.</p>
						<label for="encode-eg" class="uk-form-label ot-doc-label">API Call with Encode Type (urlLegacy, url3986, base64, none):</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-eg" class="uk-input ot-doc-eg" value="<?=$base_url?>api.php?amount=10&amp;encode=url3986" readonly="">
						</div>
						<p>Example Sentence (Non Encoded): &lsquo;Don't forget that π = 3.14 &amp; doesn't equal 3.&rsquo;</p>
						<label for="encode-default" class="uk-form-label ot-doc-label">Default Encoding (HTML Codes):</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-default" class="uk-input ot-doc-eg" value="Don&amp;‌#039;t forget that &amp;‌pi; = 3.14 &amp;‌amp; doesn&amp;‌#039;t equal 3." readonly="">
						</div>
						<label for="encode-legacy" class="uk-form-label ot-doc-label">Legacy URL Encoding:</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-legacy" class="uk-input ot-doc-eg" value="Don%27t+forget+that+%CF%80+%3D+3.14+%26+doesn%27t+equal+3." readonly="">
						</div>
						<label for="encode-url" class="uk-form-label ot-doc-label">URL Encoding (<a href='https://www.ietf.org/rfc/rfc3986.txt'>RFC 3986</a>):</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-url" class="uk-input ot-doc-eg" value="Don%27t%20forget%20that%20%CF%80%20%3D%203.14%20%26%20doesn%27t%20equal%203." readonly="">
						</div>
						<label for="encode-base64" class="uk-form-label ot-doc-label">Base64 Encoding:</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-base64" class="uk-input ot-doc-eg" value="RG9uJ3QgZm9yZ2V0IHRoYXQgz4AgPSAzLjE0ICYgZG9lc24ndCBlcXVhbCAzLg==" readonly="">
						</div>
						<label for="encode-none" class="uk-form-label ot-doc-label">None:</label>
						<div class="uk-form-controls">
							<input type="text" id="encode-none" class="uk-input ot-doc-eg" value="Don't forget that xcfx80 = 3.14 & doesn't equal 3." readonly="">
						</div>
						<h3>API Helper Tools</h3>
						<p>There are some functions in the API which can be useful to developers.</p>
						<label for="lookup-category" class="uk-form-label ot-doc-label"><b>Category Lookup:</b> Returns the entire list of categories and ids in the database.</label>
						<div class="uk-form-controls">
							<input type="text" id="lookup-category" class="uk-input ot-doc-eg" value="<?=$base_url?>api_category.php" readonly="">
						</div>
						<label for="lookup-category-q-count" class="uk-form-label ot-doc-label"><b>Category Question Count Lookup:</b> Returns the number of questions in the database, in a specific category.</label>
						<div class="uk-form-controls">
							<input type="text" id="lookup-category-q-count" class="uk-input ot-doc-eg" value="<?=$base_url?>api_count.php?category=CATEGORY_ID_HERE" readonly="">
						</div>
						<label for="lookup-global-q-count" class="uk-form-label ot-doc-label"><b>Global Question Count Lookup:</b> Returns the number of ALL questions in the database. Note: while <a href='https://opentdb.com/'>The Open Trivia Database</a> provides additional counts for Pending, Verified, and Rejected questions, The Open Triviata Database provides only verified questions.</label>
						<div class="uk-form-controls">
							<input type="text" id="lookup-global-q-count" class="uk-input ot-doc-eg" value="<?=$base_url?>api_count_global.php" readonly="">
						</div>
						<h3>Limitations</h3>
						<ul class="uk-list uk-list-divider">
							<li>1 category per request. To get questions from any category, omit parameter.</li>
							<li>Maximum of 50 Questions per request.</li>
							<li>Maximum of <?=$apiconfig['req_per_minute']?> requests per minute.</li>
						</ul>
					</div>
				</li>
			</ul>
			<h2>Build a Request</h2>
			<div uk-filter="target: .req-type-filter">
				<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-scale-up">
					<li><a href="#">Using Question IDs</a></li>
					<li><a href="#">Using Parameters</a></li>
				</ul>
				<ul class="uk-switcher uk-margin">
					<li>
						<form method="post" class="uk-form-stacked" style="display: block;">
							<div class="uk-margin">
								<label for="ids" class="uk-form-label">Question ID Numbers:</label>
								<div class="uk-form-controls">
									<input type="text" id="ids" class="uk-input" name="ids" pattern="^\d+(,\d+)*$" placeholder="Comma-separated id numbers" required>
								</div>
							</div>
							<div class="uk-margin">
								<label for="ids-encode" class="uk-form-label">Select Encoding:</label>
								<div class="uk-form-controls">
									<select name="encode" id="ids-encode" class="uk-select">
										<option value="default">Default Encoding</option>
										<option value="urlLegacy">Legacy URL Encoding</option>
										<option value="url3986">URL Encoding (RFC 3986)</option>
										<option value="base64">Base64 Encoding</option>
										<option value="none">None</option>
									</select>
								</div>
							</div>
							<input type="hidden" name="token" value="<?php echo $form_token; ?>">
							<button class="uk-button uk-button-default" value="Generate API URL">Generate API URL</button>
						</form>
					</li>
					<li>
						<form method="post" class="uk-form-stacked" style="display: block;">
							<div class="uk-margin">
								<label for="amount" class="uk-form-label">Number of Questions:</label>
								<div class="uk-form-controls">
									<input type="number" id="amount" class="uk-input uk-width-1-1" name="amount" value="30" min="0" max="50">
								</div>
							</div>
							<div class="uk-margin">
								<label for="category" class="uk-form-label">Select Category:</label>
								<div class="uk-form-controls">
									<select name="category" id="category" class="uk-select">
										<?php echo $category_options; ?>
									</select>
								</div>
							</div>
							<div class="uk-margin">
								<label for="difficulty" class="uk-form-label">Select Difficulty:</label>
								<div class="uk-form-controls">
									<select name="difficulty" id="difficulty" class="uk-select">
										<option value="any">Any Difficulty</option>
										<option value="easy">Easy</option>
										<option value="medium">Medium</option>
										<option value="hard">Hard</option>
									</select>
								</div>
							</div>
							<div class="uk-margin">
								<label for="type" class="uk-form-label">Select Type:</label>
								<div class="uk-form-controls">
									<select name="type" id="type" class="uk-select">
										<option value="any">Any Type</option>
										<option value="multiple">Multiple Choice</option>
										<option value="boolean">True / False</option>
									</select>
								</div>
							</div>
							<div class="uk-margin">
								<label for="encode" class="uk-form-label">Select Encoding:</label>
								<div class="uk-form-controls">
									<select name="encode" id="encode" class="uk-select">
										<option value="default">Default Encoding</option>
										<option value="urlLegacy">Legacy URL Encoding</option>
										<option value="url3986">URL Encoding (RFC 3986)</option>
										<option value="base64">Base64 Encoding</option>
										<option value="none">None</option>
									</select>
								</div>
							</div>
							<input type="hidden" name="token" value="<?php echo $form_token; ?>">
							<button class="uk-button uk-button-default" value="Generate API URL">Generate API URL</button>
						</form>
					</li>
				</ul>
			</div>
		</div>
	</body>
</html>