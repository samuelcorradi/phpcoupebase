<?php

namespace Coupe;

final class AppServer extends \Coupe\Http\Server
{

	/**
	 * Array que armazena todas
	 * as funcoes para cada
	 * caminho de cada metodo.
	 */
	public $reg = array();

	/**
	 * Os registros das funções são feitas
	 * em uma fila.
	 */
	// protected $_regClousure = array();

	/**
	 * Versão do seu aplicativo.
	 */
	public $version = "1.0";

	/**
	 * Esta aplicação deve ser executada
	 * como uma única instancia.
	 */
	public static function getInstance($port, \Coupe\Http\Request $req, \Coupe\Http\Response $res)
	{

		static $inst;

		if( empty($inst) )
		{
			$inst = new static($port, $req, $res);
		}

		return $inst;

	}

	/**
	 * Qualquer método invocado que não existir
	 * será tratado como a tentativa de adicionar
	 * uma nova rota usando o nome da função
	 * para determinar o método HTTP.
	 * Ex.: $app->get(), ->post(), etc. 
	 */
	public function __call($name, $arguments)
    {

    	/*
    	 * Rotas para métodos HTTP.
    	 */
    	if( preg_match('~^[a-z]+$~i', $name))
    	{
			return $this->_register(strtoupper($name), $arguments[0], $arguments[1], $arguments[2]);
		}

		throw new MemberAccessException('Method ' . $name . ' not exists.');

    }


	/**
	 * Essa função deve restornar
	 * o caminho requisitado no
	 * servidor levando em consideração
	 * a possibilidade da aplicação
	 * estar usando mode_rewiter.
	 * O caminho é útil durante a
	 * construção de aplicações web.
	 */
	public function path()
	{

		$script_path = explode("/", trim($_SERVER['SCRIPT_NAME'], "/"));

		$request_path = explode("/", trim($_SERVER['REDIRECT_URL'], "/"));

		$trimpos = 0;

		foreach ($request_path as $k => $v)
		{
			if($request_path[$k]!=$script_path[$k])
			{
				$trimpos = $k; break;
			}
		}

		return "/" . implode(array_slice($request_path, $trimpos), "/");


		// $pathbase = str_replace('/' . basename($_SERVER['SCRIPT_NAME']), '', $_SERVER["PHP_SELF"]);

		// $path = str_replace($pathbase, '', $_SERVER["REQUEST_URI"]);

		// return str_replace($_SERVER["QUERY_STRING"], '', $path);

	}

    /**
     * Registra metodos, rotas com seus middlewares e funcao. 
     */
	protected function _register($method, $route, $opts, $func=NULL)
	{

		if( is_callable($opts) )
		{
			$opts = array('function'=>$opts);
		}
		elseif ( is_array($opts) && is_callable($func) )
		{
			$opts['function'] = $func;
		}
		
		if( ! array_key_exists('function', $opts) )
		{
			throw new \Exception("É preciso fornecer uma função como parâmetro.", 1);
		}

		if( ! array_key_exists($method, $this->reg) ) // if( array_key_exists($method, $this->request) )
		{
			$this->reg[ $method ] = array();
		}

		$this->reg[ $method ][ $route ] = $opts;

		return $this;

	}

	/**
	 * Executa o programa e gera a resposta.
	 */
	public function run()
	{

		$find = false;

		$method = $this->request->getMethod();

		foreach((array)$this->reg[ $method ] as $route=>$opts)
		{

			$pattern = "/^" . str_replace(array("/", "*", "%any", "%part", "%num"), array("\/", ".*", "(.*)", "([^\/]*)", "([0-9]+)"), $route) . "\/?$/";
			
			if( preg_match($pattern, $this->path(), $m) )
			{

				call_user_func_array($opts['function'], array_slice($m, 1));
				
				$find = true;
				
				break;

			}
		
			// TODO: implementar execucao de middlewares
			if( ! array_key_exists('middleware', $opts) )
			{
				
			}
			
		}

		// TODO: fazer redirect para pagina de erro 404
		if( ! $find )
		{
			throw new \Exception("Caminho não encontrado.", 1);
		}

		parent::run();
		
		exit(0);

	}
	
}

?>