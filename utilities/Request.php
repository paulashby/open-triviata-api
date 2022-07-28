<?php

Class Request {

	//TODO: Include token in PARAMS array - this is alphanumeric, so will need to revisit the clean() method

	private const PARAMS = array(
		// Could store validation functions in arrays for amount, category, token, but then how do we distinguish from the enumerated values
		// the firt array element would be an object on the functions vs a string on the enumerations
		'amount' 	 => 'validateNumeric',
		'category' 	 => 'validateNumeric',
		'token' 	 => 'validateAlphanumeric',
		'difficulty' => array(
			"easy",
			"medium",
			"hard"
		),
		'type' 		 => array(
			"multiple",
			"boolean"
		),
		'encode' 	 => array(
			'urlLegacy',
			'base64',
			'url3986'
		)
	);

	private $query_config = array(
		'attributes' => array()
	);
	
	public function __construct($url) {
		// Parse parameters and store in $query_params array
		$parts = parse_url($url, PHP_URL_QUERY);
		parse_str($parts, $query_params);

		// Validate (and effectively sanitise) values
		$this->clean($query_params);		
	}
	public function breakdown() {
		return $this->query_config;
	}

	/**
	 * Validate (and effectively sanitise) input data
	 *
	 * @param array $query_params: parameter name/value pairs
	 */ 
	public function clean($query_params) {

		foreach ($query_params as $param_name => $param_value) {

			// Validate parameter name
			if (!array_key_exists($param_name, self::PARAMS)) {
				die($this->invalidParameter());
			}
			// Parameter name accepted			
			if (is_array(self::PARAMS[$param_name])) {
				// entry for this parameter name includes a list of expected values - make sure submitted value matches one of these
				if (!in_array($param_value, self::PARAMS[$param_name])) {
					die($this->invalidParameter());
				}
				// Parameter value accepted
				if ($param_name === 'encode') {
					// encode is stored separately so we don't pollute $query_config['attributes'] which is used to build WHERE clause of MySQL query
					$this->query_config['encode'] = $param_value;
					continue;
				}
				$this->query_config['attributes'][$param_name] = $param_value;
				continue;
			}
			// Use provided validation function
			call_user_func(array($this, self::PARAMS[$param_name]), $param_value);

			if ($param_name === 'amount' || $param_name === 'token') {
				// amount is stored separately so we don't pollute $query_config['attributes'] which is used to build WHERE clause of MySQL query
				$this->query_config[$param_name] = $param_value;
				continue;
			} 
			$this->query_config['attributes'][$param_name] = $param_value;
		}
	}
	
	/**
	 * Die if not numeric
	 * 
	 * @param string $param_value
	 */ 
	private function validateNumeric($param_value) {
		if (!is_numeric($param_value)) {
			die($this->invalidParameter());
		}
	}
	
	/**
	 * Die if not alphanumeric
	 * 
	 * @param string $param_value
	 */ 
	private function validateAlphanumeric($param_value) {
		if (!ctype_alnum($param_value)) {
			die($this->invalidParameter());
		}
	}

	/**
	 * Provide Invalid Parameter response
	 *
	 * @return Array containing invalid parameter response code and empty results array
	 */ 
	private function invalidParameter() {
		return json_encode(array(
			'response_code' => 2,
			'results' => array()
		));
	}
}