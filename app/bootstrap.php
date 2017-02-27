<?php

/*
* O PHP Coupé suporta apenas a versão 5 do PHP.
*/
if( version_compare(PHP_VERSION, "5.0")<0 )
{
	trigger_error("PHP Coupé only works with PHP 5.0 or newer", E_USER_ERROR);
}

/*
*  Versão atual do PHP Coupé
*/
define("COUPE", "0.7");

require "../lib/Coupe/Http/Request.php";

require "../lib/Coupe/Http/Response.php";

require "../lib/Coupe/Http/Server.php";

require "../lib/Coupe/AppServer.php";

$app = \Coupe\AppServer::getInstance(\Coupe\AppServer::ENV_PORT, \Coupe\Http\Request::createFromEnv(), new \Coupe\Http\Response(\Coupe\Http\Response::HTTP_ENV));

include_once "routes.php";

$app->run();

?>