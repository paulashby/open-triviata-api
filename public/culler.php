<?php

$candidates = array(
    array(
        'directory' => realpath(__DIR__ . "/../") . "/req_log",
        'life'      => 300
    ),
    array(
        'directory' => realpath(__DIR__ . "/../") . "/token_data",
        'life'      => 21600
    )
);

foreach ($candidates as $candidate) {
    cull($candidate['directory'], $candidate['life']);
}

function cull($directory, $life) {

    if ($handle = opendir($directory)) {

        $nowish = time();

        while (false !== ($file = readdir($handle))) {

            if ($file != "." && $file != "..") {

                $current_file = $directory . "/" . $file;

                if ($nowish - filemtime($current_file) > $life) {
                    // file has expired
                    if (!unlink($current_file)) {
                        error_log("Unable to delete file $current_file");
                    }
                }
            }
        }
        closedir($handle);
    }
}