<?php

namespace Coupe\Http;

class Server
{

	const ENV_PORT = 0;

	/**
	 * Objeto com as informações
	 * de requisição do cliente.
	 */
	public $request;

	/**
	 * Objeto com as informações
	 * de resposta do servidor.
	 */
	public $response;

	/**
	 * Porta na qual a aplicação
	 * servidora irá escutar.
	 */
	protected $_port;

	/**
	 * Método construtor privado
	 * para implementar singleton.
	 */
	public function __construct($port=0, \Coupe\Http\Request $req, \Coupe\Http\Response $res)
	{

		$this->setPort($port);

		$this->request = $req;

		$this->response = $res;
		
	}

	/*
	* Decide o formato de mensagem
	* de acordo com os formatos
	* suportados por requisição
	* resposta.
	*/
	public function getResponseFormat()
	{

		foreach($this->request->getAccepted() as $f)
		{
			if( in_array($f, $this->supported_formats) ) break;
		}

		return $f;

	}

	/**
	 * Define a porta a ser utilizada pela
	 * aplicacao. Caso seja passado 0, ou
	 * a constante self::ENV_POST, tenta
	 * utilizar a porta do servidor web
	 * na qual a aplicação já está sendo
	 * executada.
	 */
	public function setPort($p)
	{

		if($p===static::ENV_PORT)
		{

			if (! isset($_SERVER['SERVER_PORT']))
			{
				throw new \Exception("Can't find the environment HTTP port.", 1);
			}

			$p = $_SERVER['SERVER_PORT'];

		}

		$this->_port = (int)$p;

		if(!$this->isValidPort())
		{
			throw new \Exception("Invalid HTTP port.", 1);
		}

		return $this;

	}

	/**
	 * Retorna a porta na qual o servidor
	 * estah sendo executado.
	 */
	public function getPort()
	{

		return $this->_port;

	}

	/**
	 * Diz se a porta passada eh
	 * uma porta HTTP valida.
	 */
	public function isValidPort()
	{

		return ctype_digit((string) $this->_port) and 1 <= $this->_port and $this->_port <= 65535; //true;

	}

	/**
	* Retorna o número 
	*
	* @return string Endereço IP de quem criou a requisição.
	*/
	public function clientHost()
	{

		$h = '';

		if (getenv('HTTP_CLIENT_IP'))
			$h = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$h = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$h = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$h = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$h = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$h = getenv('REMOTE_ADDR');
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
			$h = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$h = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$h = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$h = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$h = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$h = $_SERVER['REMOTE_ADDR'];

		return $h;
		
	}

	/**
	 * Processa o cabeçalho de resposta.
	 */
	public function run()
	{

		$res = & $this->response;

		/*
		 * Define a primeira linha.
		 */
		if(function_exists(http_response_code))
		{
			http_response_code($res->getStatus());
		}
		else
		{
			header("HTTP/" . $res->getVersion() . ' ' . $res->getStatus() . ' ' . $res->getStatusText(), true, $res->getStatus());
		}

		/*
		 * Adiciona os cabeçalhos.
		 */
		foreach($res->header as $f => $v)
		{
			header("{$f}: {$v}\r\n");
		}

		/*
		 * Adiciona o corpo da resposta.
		 */
		echo $res->getBody();

		return true;

	}
	
}

?>