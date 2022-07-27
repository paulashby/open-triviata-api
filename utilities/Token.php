<?php

Class Token {

	// Token expires after 6 hours of inactivity
	private const LIFE = 21600;

	private $token_name;
	private $token_file;

	// Save the file
	// file_put_contents($log_info['log_file'], json_encode($log));
	
	public function __construct($token_name = false) {

		// Could be called by api_token for a token request or reset - in which case, $token will be false for request, string for reset
		// OR
		// Called by main api endpoint, in which case, token will be string

		$token_request = $token_name === false;

		$this->token_name = $token_request ? bin2hex(random_bytes(32)) : $token_name;
		$this->token_file = $token_file = realpath(__DIR__ . "/../") . "/token_data/" . $this->token_name . ".json";

		if ($token_request) {
			file_put_contents($token_file, json_encode(array()));
		} else {
			if (!file_exists($token_file)) {
				// Expired token files may have been deleted by cron job.
				//TODO: need cron job on the unlimited server to remove token files when they're older than 6 hours
				$this->notFound();
			}

			$now = time();
			
			if ($now - filemtime($token_file) > self::LIFE) {
				// Token has expired - delete file
				if (!unlink($token_file)) {
					error_log("Unable to delete token file $token_file");
				}
				$this->notFound();
			}
		}
	}

	/**
	* Get already retrieved question ids for this token
	*
	* @return String of comma delimited question ids
	*/ 
	public function retrieved() {
		return implode(", ", $this->tokenData());
	}

	/**
	* Get token name (the bin2hex string used to name the token file)
	*
	* @return bin2hex string - effectively, the token as far as user is concerned
	*/ 
	public function tokenName() {
		return $this->token_name;
	}

	/**
	* Assign question ids to this token
	*/ 
	public function update($retrieved) {
		$ids = array_merge($this->tokenData(), $retrieved);
		file_put_contents($this->token_file, json_encode($ids));
	}

	/**
	* Reset the current token ('wipe all past memory')
	*/ 
	public function reset() {
		file_put_contents($this->token_file, json_encode(array()));
	}

	/**
	* Get existing data for this token
	*
	* @return array of token data or empty array if no data has yet been stored in the token file
	*/ 
	public function tokenData() {
		$token_data = file($this->token_file);
		return is_array($token_data) ? json_decode($token_data[0]) : array();
	}

	private function notFound() {
		die(json_encode(array(
			"response_code" => 3,
			"results" => array()
		)));
	}
}