<?php

Class Encoder {

	/**
	 * Encode given string with specified method
	 *
	 * @param string $str: The string to encode
	 * @param string $method: The name of the encoding method to use
	 * @return string - the encoded string
	 */ 
	public function encode ($str, $method) {
		// Decode entities before re-encoding
		return call_user_func(array($this, $method), html_entity_decode($str));
	}

	private function default ($str) {
		return htmlentities($str, ENT_QUOTES);
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