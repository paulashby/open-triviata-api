<?php

Class Validator {

	private const PARAMS = array(
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

	private $query_config;
	private $max_questions;
	
	public function __construct($url, $max_questions) {

		$this->max_questions = $max_questions;

		// Parse parameters and store in $query_params array
		$parts = parse_url($url, PHP_URL_QUERY);
		parse_str($parts, $query_params);

		// Update $query_config with validated (and effectively sanitised) input values
		$this->query_config = $this->makeCleanConfig($query_params);
	}
	public function validate() {
		return $this->query_config;
	}

	/**
	 * Route data to appropriate cleaning method
	 *
	 * @param array $query_params: parameter name/value pairs
	 */ 
	private function makeCleanConfig($query_params) {

		$query_config = array(
			'attributes'	=> array(),
			'encode' 		=> 'default'
		);

		return array_key_exists('ids', $query_params) ? $this->cleanIDs($query_params, $query_config) : $this->cleanParams($query_params, $query_config);

	}

	/**
	 * Validate (and effectively sanitise) IDs and encoding type
	 *
	 * @param array $query_params: parameter name/value pairs
	 */ 
	private function cleanIDs($query_params, $query_config) {

		$ids = explode(',', $query_params['ids']);

		$query_config['ids'] = array_filter($ids, array($this, 'validateNumeric'));
		$query_config['ids'] = $ids;

		if (isset($query_params['encode'])) {

			$encode = $query_params['encode'];

			if (!in_array($encode, self::PARAMS['encode'])) {
				die($this->invalidParameter());
			}
			$query_config['encode'] = $encode;
		}
		return $query_config;
	}

	/**
	 * Validate (and effectively sanitise) input data
	 *
	 * @param array $query_params: parameter name/value pairs
	 */ 
	private function cleanParams($query_params, $query_config) {

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
					$query_config['encode'] = $param_value;
					continue;
				}
				$query_config['attributes'][$param_name] = $param_value;
				continue;
			}
			// Use provided validation function
			call_user_func(array($this, self::PARAMS[$param_name]), $param_value);

			if ($param_name === 'amount' || $param_name === 'token') {
				// amount is stored separately so we don't pollute $query_config['attributes'] which is used to build WHERE clause of MySQL query
				$query_config[$param_name] = $param_value;
				continue;
			} 			
			$query_config['attributes'][$param_name] = $param_value;
		}
		// Ensure amount is set and within limit
		if (isset($query_config['amount'])) {
			$query_config['amount'] = min($query_config['amount'], $this->max_questions);
		} else {
			$query_config['amount'] = $this->max_questions;
		}
		return $query_config;
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