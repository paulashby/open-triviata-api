<?php

Class Token {

	// Token expires after 6 hours of inactivity
	private const LIFE = 21600;

	private $token_name;
	private $token_file;

	public function __construct($token_name = false) {

		$token_request = $token_name === false;

		$this->token_name = $token_request ? bin2hex(random_bytes(32)) : $token_name;
		$this->token_file = $token_file = realpath(__DIR__ . "/../") . "/token_data/" . $this->token_name . ".json";

		if ($token_request) {
			file_put_contents($token_file, json_encode(array()));
		} else {
			if (!file_exists($token_file)) {
				// Expired token files may have been deleted by cron job.
				$this->notFound();
			}

			$now = time();
			
			if ($now - filemtime($token_file) > self::LIFE) {
				// Token has expired - delete file
				if (!unlink($token_file)) {
					error_log("Unable to delete token file $token_file");
				}
				// Regardless of the outcome of the preceding unlink operation, this is still a show-stopper as the submitted token has expired
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
		return implode(", ", $this->data());
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

		$token_data = $this->data();

		if (is_array($token_data)) {
			// Merge latest ids with existing record
			$retrieved = array_merge($token_data, $retrieved);
		}		
		file_put_contents($this->token_file, json_encode($retrieved));
	}

	/**
	 * Reset the current token ('wipe all past memory')
	 * 
	 * @return Number of bytes written to the file, or false on failure
	 */ 
	public function reset() {
		return file_put_contents($this->token_file, json_encode(array()));
	}

	/**
	 * Get existing data for this token
	 *
	 * @return Array of question ids or empty array if no data has yet been stored in the token file
	 */ 
	private function data() {
		$token_data = file($this->token_file);
		return is_array($token_data) ? json_decode($token_data[0]) : array();
	}

	/**
	 * Provide Not Found response
	 *
	 * @return Array containing not found response code and empty results array
	 */ 
	private function notFound() {
		die(json_encode(array(
			"response_code" => 3,
			"results" => array()
		)));
	}
}