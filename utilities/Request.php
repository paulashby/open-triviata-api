<?php

Class Request {

	//TODO: Include token in PARAMS array - this is alphanumeric, so will need to revisit the clean() method
	
	private const PARAMS = array(
		'amount' => "int",
		'category' => "int",
		'difficulty' => array(
			"easy",
			"medium",
			"hard"
		),
		'type' => array(
			"multiple",
			"boolean"
		),
		'encode' => array(
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
			// Value should be numeric
			if (!is_numeric($param_value)) {
				die($this->invalidParameter());
			}
			if ($param_name === 'amount') {
				// amount is stored separately so we don't pollute $query_config['attributes'] which is used to build WHERE clause of MySQL query
				$this->query_config['amount'] = $param_value;
				continue;
			} 
			$this->query_config['attributes'][$param_name] = $param_value;
		}
	}
	/**
	 * Provide invalid parameter response
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