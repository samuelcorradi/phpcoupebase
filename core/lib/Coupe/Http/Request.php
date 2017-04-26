<?php

namespace Coupe\Http;

include "Url.php";

/*
* Classe provê uma interface fácil
* para envio de requisições HTTP.
* Suporta instruções simples suportadas
* pelo PHP, bem como envio de arquivos
* e autenticação.
* Classe que manipula os dados
* de requisição do cliente.
* Como não tem acesso a recursos
* profundos de um componente
* HTTP vinculado ao servidor web,
* confia basicamente nos dados
* disponibilizados através do
* array global $_SERVER.
*/
class Request
{

	/**
	 * Armazena o endereco da requisicao.
	 */
	protected $_url;

	/**
	 * Método de requisição que o cliente
	 * deverá utilizar para realizar
	 * a requisição.
	 */
	protected $_method;

	/**
	 * Cabeçalho da resposta.
	 */
	protected $_header = array();

	/**
	 * Retorna um objeto de requisicao
	 * utilizando informacoes da
	 * requisicao definidas no ambiente
	 * da aplicacao servidora.
	 */
	public static function createFromEnv()
	{

		if ( ! isset($_SERVER['REQUEST_URI']) )
		{
			throw new \Exception("Can't find environment request data.", 1);
		}
	
		// $scheme = ( isset($_SERVER['SERVER_PROTOCOL']) && strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')===false ) ? 'http://' : 'https://';
	
		// $host = ( isset($_SERVER['HTTP_HOST']) ) ? $_SERVER['HTTP_HOST'] : '';
	
		// $port = ( isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT']!=80 ) ? ':' . $_SERVER['SERVER_PORT'] : '';
	
		// $path = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : '';
	
		// $url = new \Coupe\Http\Url($scheme . $host . $port . $path);

		$req = new \Coupe\Http\Request($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], static::getEnvHeaders());

		$req->setBody(file_get_contents('php://input'));

		return $req;

	}

	/**
	 *
	 */
	public function __construct($url, $method="GET", Array $header=array())
	{

		$this->_url = $url;

		$this->setMethod($method);

		foreach ($header as $k => $v)
		{
			$this->setHeader($k, $v);
		}

	}

	/**
	 * Acionar cabeçalho.
	 */
	public function setHeader($k, $v)
	{

		$k = ucfirst(strtolower($k));

		$this->header[$k] = $v;

		return $this;

	}

	/**
	 * Acionar cabeçalho.
	 */
	public function getHeader($k)
	{

		if( array_key_exists($k, $this->_header) )
		{
			return $this->_header[$k];
		}

	}

	/**
	 *
	 */
	public function url()
	{

		return $this->url;

	}

	


	/**
	 * Essa função deve restornar
	 * o caminho requisitado no
	 * servidor levando em consideração
	 * a possibilidade da aplicação
	 * estar usando mod_rewiter.
	 * O caminho é útil durante a
	 * construção de aplicações web.
	 */
	public function path()
	{

		// TODO: verificar esta funcao. O que acontece quando
		// o framework for movido de pasta?
		if($_SERVER['REDIRECT_URL']==$_SERVER['REQUEST_URI'])
		{
			return '/';
		}

		$script_path = explode("/", trim($_SERVER['SCRIPT_NAME'], "/"));

		$request_path = explode("/", trim($_SERVER['REDIRECT_URL'], "/"));

		// echo "<pre>" . print_r($_SERVER) . "</pre>";

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
	* Pega os dados submetidos pelo
	* cliente.
	*/
	// public function getData()
	// {

	// 	if($this->isGet())
	// 	{
	// 		return $_GET;
	// 	}
	// 	elseif($this->isPost())
	// 	{
	// 		return $_POST;
	// 	}
	// 	else
	// 	{
	// 		return $_REQUEST;
	// 	}

	// }

	/**
	 * Retorna oos cabeçalhos da requisicao
	 * feita e que jah estao disponiveis
	 * no ambiente do servidor.
	 */
	public static function getEnvHeaders()
	{

		if(function_exists('getallheaders'))
		{
			return getallheaders();
		}

		$h = array();

		$copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($_SERVER as $k => $v)
        {

            if (substr($k, 0, 5) === 'HTTP_')
            {

                $k = substr($k, 5);

                if ( ! isset($copy_server[$k]) || ! isset($_SERVER[$k]) )
                {

                    $k = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $k))));

                    $h[$k] = $v;

                }

            }
            elseif ( isset($copy_server[$k]) )
            {
                $h[$copy_server[$k]] = $v;
            }

        }

        return $h;

	}

	/**
	 * Retorna o corpo da requisição.
	 */
	public function setBody($b)
	{

		$this->_body = (string)$b;

		return $this;

	}

	/**
	 * Retorna o corpo da requisição.
	 */
	public function getBody()
	{

		return $this->_body;

	}

	/**
	 * Retonar o tipo da requisição.
	 */
	public function setMethod($m)
	{

		$this->_method = strtoupper($m);

		return $this;

	}

	/**
	 * Retonar o tipo da requisição.
	 */
	public function getMethod()
	{

		return $this->_method;

	}

	/**
	 * Método mágico é usado para avaliar
	 * o  isPost(), isGet()
	 */
	public function __call($name, $arguments)
	{

		/*
		* Rotas para métodos HTTP.
		*/
		if( preg_match('~^is([A-Z][a-z]+)$~i', $name, $m))
		{
			return ($this->_method === strtoupper($m[1]));
		}

		// throw new \MemberAccessException('Method ' . $methodName . ' not exists.');
		throw new \Exception("Method '" . $name . "' not exists.");

	}



	/**
	* Retorna o valor de um determinado cookie.
	*/
	// public function cookie($k)
	// {

	// 	return $_COOKIE[$k];

	// }

	/**
	* Retorna a lista de charsets suportados.
	*/
	public function charset()
	{

		return $this->_header['Accept-Charset'];

	}

	/**
	* Define qual codificação o cliente aceita.
	* Dessa forma o servidor pode utilizar
	* algum método de compactação de conteúdo,
	* e assim economizar banda de rede.
	*/
	public function encoding()
	{

		return $this->_header['Accept-Encoding'];

	}

	/**
	* Define os idiomas preferidos pelo
	* cliente para negociação com o
	* servidor.
	*/
	public function language()
	{

		return $this->_header['Accept-Language'];

	}

	/**
	* Define os formatos de documento
	* aceitos pelo cliente. A reposta
	* do servidor deverá corresponder
	* a esse formato.
	*/
	public function accept()
	{

		return $this->_header['Accept'];

	}

	/**
	 * Determina se a requisicao estah
	 * enviado JSON.
	 *
	 * @return bool
	 */
	public function isJson()
	{
	}

}

?>