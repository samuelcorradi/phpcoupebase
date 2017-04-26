<?php

$app->get("*", function() use($app)
{

	require_once "../../phpcoupecms/core/lib/Coupe/CMS.php";

	require_once "../../phpcoupecms/core/lib/Habilis/File.php";

	require_once "../../phpcoupecms/core/lib/Coupe/CMS/Adapter.php";

	require_once "../../phpcoupecms/core/lib/Coupe/CMS/Adapter/File.php";

	$cms = Coupe\CMS::getInstance('/Users/samuelcorradi/Sites/phpcoupecms/app/', '/Users/samuelcorradi/Sites/phpcoupecms/shared/', '/Users/samuelcorradi/Sites/phpcoupecms/public/');

	$adapter = new \Coupe\CMS\Adapter\File('/Users/samuelcorradi/Sites/phpcoupecms/app/');

	$cms->setAdapter($adapter);

	$cms->loadConfig('devel');

	$cms->setData('teste', 'DSADAS');

	// $app->response->appendBody("a");

	$html = $cms->loadPage($app->request->path());

	$app->response->appendBody($html);

});

/*
$app->get("/", function() use($app)
{

	$app->response->appendBody("a");

});

$app->get("/teste/delete", array('middleware'=>'auth'), function() use($app)
{

	$app->response->setBody("dsdsadsa");

	// print_r($_SERVER);

	print_r($app->request->path());

	// print_r($app->request->isGet());

	// print_r($app->getPort());

	// print_r($app->response->getVersion());

	// print_r($app->clientHost());

});

$app->post("/teste/delet", array('function'=>function() use($app)
{

}));

$app->get("/home/%part/%part", function($a, $b) use($app)
{

	echo $a;
	echo $b;
	echo $app->version;

	echo "Uma página estática qualquer.";

});
*/



?>