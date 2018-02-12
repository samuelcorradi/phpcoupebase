<?php

// print_r($_SERVER);

error_reporting(E_ALL || E_STRICT);
/*
*  Versão atual do PHP Coupé
*/
define("COUPE", "0.7");

define('APP_NAME', 'app');

define('DS', DIRECTORY_SEPARATOR);

define('BASE_FOLDER', str_replace('/public/index.php', '', $_SERVER['SCRIPT_FILENAME'] . DS));

define('LIB_FOLDER', BASE_FOLDER . 'core' . DS . 'lib' . DS);

define('APP_FOLDER', BASE_FOLDER . APP_NAME . DS);

spl_autoload_register(function($class)
{

	$path = (strrpos($class, "Coupe\Middleware\\")===0) ? APP_FOLDER . 'middleware' . DS . substr($class, strrpos($class, "\\") + 1) : LIB_FOLDER . str_replace("\\", DS, $class);

	include_once $path . ".php";

});

include_once '../core/bootstrap.php';

?>