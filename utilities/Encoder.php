<?php

Class Encoder {

	/**
	 * Encode question item
	 *
	 * @param associative array $question_item: question details - category, difficulty etc (the entry for incorrect answers is an array)
	 * @param string $method: the name of the required encoding method
	 * @return associative array with encoded values
	 */ 
	public function encodeItem($question_item, $method) {

		// Encode assembled question
		foreach ($question_item as $attribute => $attribute_value) {
			if (is_array($attribute_value)) {
				// Encode indiviual elements
				$encoded_array = array();
				foreach ($attribute_value as $attrib) {
					$encoded_array[] = $this->encode($attrib, $method);
				}
				$question_item[$attribute] = $encoded_array;
			} else {
				$question_item[$attribute] = $this->encode($attribute_value, $method);
			}
		}
		return $question_item;
	}

	/**
	 * Encode given string with specified method
	 *
	 * @param string $str: The string to encode
	 * @param string $method: The name of the encoding method to use
	 * @return string - the encoded string
	 */
	private function encode ($str, $method) {
		// Decode entities before re-encoding
		return call_user_func(array($this, $method), html_entity_decode($str, ENT_QUOTES));
	}

	private function default ($str) {
		return htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', /*double_encode*/false );
	}

	private function urlLegacy ($str) {
		return urlencode($str);			
	}

	private function url3986 ($str) {
		return rawurlencode($str);
	}

	private function base64 ($str) {
		return base64_encode($str);
	}
}