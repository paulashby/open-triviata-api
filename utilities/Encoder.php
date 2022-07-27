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
		return call_user_func(array($this, $method), $str);
	}

	public function urlLegacy ($str) {
		return urlencode($str);
	}

	public function url3986 ($str) {
		return rawurlencode($str);
	}

	public function base64 ($str) {
		return base64_encode($str);
	}
}