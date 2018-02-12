<?php

/*
* O PHP Coupé suporta apenas a versão 5 do PHP.
*/
if( version_compare(PHP_VERSION, "5.0")<0 )
{
	trigger_error("PHP Coupé only works with PHP 5.0 or newer", E_USER_ERROR);
}

$app = \Coupe\AppServer::getInstance(\Coupe\AppServer::ENV_PORT, \Coupe\Http\Request::createFromEnv(), new \Coupe\Http\Response(\Coupe\Http\Response::HTTP_ENV));

include_once APP_FOLDER . 'routes.php';

$app->run();

?>