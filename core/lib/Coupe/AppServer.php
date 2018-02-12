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
	public function __call($name, $args)
    {

    	/*
    	 * Rotas para métodos HTTP.
    	 */
    	if( preg_match('~^[a-z]+$~i', $name))
    	{
    	
    		array_unshift($args, strtoupper($name));
    	
    		return call_user_func_array('self::_register', $args);
			
		}

		throw new MemberAccessException('Method ' . $name . ' not exists.');

    }

    /**
     * Registra metodos, rotas com seus middlewares e funcao. 
     */
	protected function _register($method, $route, $opts=array(), $func=NULL)
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

		$finded = false;

		$method = $this->request->getMethod();

		foreach((array)$this->reg[ $method ] as $route=>$opts)
		{
	
			$pattern = "/^" . str_replace(array("/", "*", "%any", "%part", "%num"), array("\/", ".*", "(.*)", "([^\/]*)", "([0-9]+)"), $route) . "\/?$/";

			// echo $this->request->path();
			
			if( preg_match($pattern, $this->request->path(), $m) )
			{ 

				call_user_func_array($opts['function'], array_slice($m, 1));

				// TODO: implementar execucao de middlewares
				if( array_key_exists('middleware', $opts) )
				{

					foreach($opts['middleware'] as $middle_name)
					{

						$refl = new \ReflectionClass("\Coupe\Middleware\\" . $middle_name);

						$inst = $refl->newInstanceArgs(array($this->request));

						$inst();

					}

				}
				
				$finded = true;
				
				break;

			}
			
		}

		// TODO: fazer redirect para pagina de erro 404
		if( ! $finded )
		{
			throw new \Exception("Caminho não encontrado.", 1);
		}

		parent::run();
		
		exit(0);

	}
	
}

?>