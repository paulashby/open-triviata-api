<!DOCTYPE html>
<html class="ot-bg uk-light">
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

	<link rel="stylesheet" href="/css/otriviata.css" type="text/css">
	<!-- <script src="scripts/otriviata.js"></script> -->

</head>
<body>
	<div class="uk-container uk-container-xsmall">
		<img src="/img/open-triviata-logo.svg" alt="Open Triviata logo" width="200" id="logo">
		<?php
			if (isset($url)) {
				echo "<div class='alert alert-success'>
					<strong>API URL Generated!: </strong><input type='text' class='form-control' value=$url readonly=''>
				</div>";
			}
		?>
		<h1>Open Triviata API</h1>
		<p>The Open Triviata Database provides a completely free JSON API for use in programming projects, providing questions sourced from <a href='https://opentdb.com/'>The Open Trivia Database</a>. If you don&apos;t need question ID numbers or unencoded data, the original Open Trivia Database will serve you perfectly well :)</p>
		<p>All data provided by the API is available under the Creative Commons Attribution-ShareAlike 4.0 International License. <a href='https://creativecommons.org/licenses/by-sa/4.0/'><img src='https://licensebuttons.net/l/by-sa/4.0/80x15.png'></a></p>
		<ul uk-accordion class="ot-doc">
		    <li>
		        <a class="uk-accordion-title ot-doc-title" href="#">API Documentation</a>
		        <div class="uk-accordion-content">
		        	<h3>Getting Started</h3>
		        	<p>To get started using the Open Triviata Database API, use this URL: </p>
	        		<div class="uk-form-controls">
	        			<input type="text" class="uk-input ot-doc-eg" value="<?=$base_url?>api.php?amount=10" readonly="">
	        		</div>
	        		<p>For more settings or help using the API, read along below. Alternatively, you can use the &lsquo;Build a Request&rsquo; form to craft your specific query.</p>
		        	<p>To retrieve one or more questions by ID number, simply use the 'ids' parameter with a comma-separated list (NOTE: all other parameters will be discarded with the exception of encode - see below):</p>
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
	        		<label for="using-st" class="uk-form-label ot-doc-label">Retrieve a Session Token:</label>
	        		<div class="uk-form-controls">
	        			<input type="text" id="using-st" class="uk-input ot-doc-eg" value="<?=$base_url?>api_token.php?command=request" readonly="">
	        		</div>
		        	<label for="using-st" class="uk-form-label ot-doc-label">Reset a Session Token:</label>
	        		<div class="uk-form-controls">
	        			<input type="text" id="using-st" class="uk-input ot-doc-eg" value="<?=$base_url?>api_token.php?command=reset&amp;token=YOURTOKENHERE" readonly="">
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
		        			<td><b>Invalid Parameter</b> One or more arguments aren&apos;t valid. (Eg amount = Five)</td>
		        		</tr>
		        		<tr>
		        			<td class="ot-code-num">3</td>
		        			<td><b>Token Not Found</b> Session Token does not exist.</td>
		        		</tr>
		        		<tr>
		        			<td class="ot-code-num">4</td>
		        			<td><b>Token Empty</b> Session Token has returned all possible questions for given query. Token should be reset.</td>
		        		</tr>
		        	</table>
		        </div>
		    </li>
		</ul>
		<h2>Build a Request</h2>
		<div uk-filter="target: .req-type-filter">
			<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-top">
				<li><a href="#">Use Question IDs</a></li>
		        <li><a href="#">Use Parameters</a></li>
		    </ul>
			<!-- <ul class="req-type-filter uk-child-width-1-2@s"> -->
			<ul class="uk-switcher uk-margin">
			    <li>
		        	<form method="post" class="uk-form-stacked" style="display: block;">
		        		<div class="uk-margin">
							<label for="ids" class="uk-form-label">Question ID Numbers:</label>
							<div class="uk-form-controls">
								<input type="text" id="ids" class="uk-input" name="ids" pattern="^\d+(,\d+)*$" placeholder="Comma-separated id numbers">
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
						<input type="hidden" name="token" value="a183298435042426354a25c5dda529c8">
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
		    						<option value="any">Any Category</option>
		    						<option value="9">General Knowledge</option>
		    						<option value="10">Entertainment: Books</option>
		    						<option value="11">Entertainment: Film</option>
		    						<option value="12">Entertainment: Music</option>
		    						<option value="13">Entertainment: Musicals &amp; Theatres</option>
		    						<option value="14">Entertainment: Television</option>
		    						<option value="15">Entertainment: Video Games</option>
		    						<option value="16">Entertainment: Board Games</option>
		    						<option value="17">Science &amp; Nature</option>
		    						<option value="18">Science: Computers</option>
		    						<option value="19">Science: Mathematics</option>
		    						<option value="20">Mythology</option>
		    						<option value="21">Sports</option>
		    						<option value="22">Geography</option>
		    						<option value="23">History</option>
		    						<option value="24">Politics</option>
		    						<option value="25">Art</option>
		    						<option value="26">Celebrities</option>
		    						<option value="27">Animals</option>
		    						<option value="28">Vehicles</option>
		    						<option value="29">Entertainment: Comics</option>
		    						<option value="30">Science: Gadgets</option>
		    						<option value="31">Entertainment: Japanese Anime &amp; Manga</option>
		    						<option value="32">Entertainment: Cartoon &amp; Animations</option>
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
	    				<input type="hidden" name="token" value="a183298435042426354a25c5dda529c8">
	    				<button class="uk-button uk-button-default" value="Generate API URL">Generate API URL</button>
	    			</form>		    		
			    </li>
			</ul>		
		</div>
	</div>

</body>
</html>