<?php

// print_r($_SERVER);

error_reporting(E_ALL | E_STRICT);

define('BASE_FOLDER', str_replace('/public/index.php', '', $_SERVER['SCRIPT_FILENAME']));

define('DS', DIRECTORY_SEPARATOR);

include_once "../core/bootstrap.php";

?>