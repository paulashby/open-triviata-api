<?php

define("LIFE", 21600);

$directory = realpath(__DIR__ . "/../") . "/token_data";

if ($handle = opendir($directory)) {

	$nowish = time();

    while (false !== ($file = readdir($handle))) {

        if ($file != "." && $file != "..") {

        	$token_file = $directory . "/" . $file;

            if ($nowish - filemtime($token_file) > LIFE) {
            	// Token has expired - delete file
            	if (!unlink($token_file)) {
            		error_log("Unable to delete token file $token_file");
            	}
            }
        }
    }
    closedir($handle);
}