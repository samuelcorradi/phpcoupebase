<?php

$app->get("/teste/delete", array('middleware'=>'auth'), function() use($app)
{

	$app->response->setBody("dsdsadsa");

	// print_r($_SERVER);

	// print_r($app->path());

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




?>