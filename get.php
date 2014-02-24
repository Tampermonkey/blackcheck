<?php

$params = $_POST;

if (empty($params)) {
    $params = $_GET;
}

define('DATA_FILE', "blacklist.json");

function readJson($myFile) {
    $theData = "{}";

    if (file_exists($myFile)) {
        $fh = fopen($myFile, 'r') or err(404, "internal (read) error!");

        $i = 0;
        $run = 1;
        $retr = 3;

        while ($run) {
            if (flock($fh, LOCK_SH)) { // do an exclusive lock
                $theData = fread($fh, filesize($myFile));
                flock($fh, LOCK_UN); // release the lock
                break;
            } else {
                if ($i++ < $retr) {
                    usleep(100 * 1000);
                } else {
                    err(404, "internal (read) error!");
                }
            }
        }

        fclose($fh);
    }

    return $theData;
}

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers

if ($params["version"]) {
    if (file_exists(DATA_FILE)) {
        $ft = filemtime(DATA_FILE);
    } else {
        $ft = 0;
    }
    print('{ "version" : "' . $ft . '" }');
} else {
    echo readJson(DATA_FILE);
}

?>