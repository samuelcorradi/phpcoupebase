<?php

/**
* Classe URL
*
* Métodos para tratamento de URLs.
*
* @version 0.1
* @package Coupe\Http
* @subpackage Navigation
* @author Samuel Corradi <falecom@samuelcorradi.com.br>
* @copyright Copyright (c) 2012, habilis.com.br
* @license http://creativecommons.org/licenses/by-nd/3.0 Creative Commons BY-ND
* @example http://www.habilis.com.br/documentation/classes/habilis/url
* @see \Habilis\Url
*/

namespace Coupe\Http;

class Url
{

	/**
	* Pega uma URL em formato string
	* e retorna com as partes separadas
	* em um array associativo.
	*
	* @access public
	* @param string $url Endereço WEB, URL.
	* @return array Dicionário com informações da URL.
	* @static
	* @see parse_url()
	*/
	public static function parse($url)
	{
	
		$parsed = parse_url((string)$url);
	
		$parsed['path'] = ( empty($parsed['path']) ) ? NULL : '/' . trim($parsed['path'], '/');
	
		return array_merge(array('scheme'=>NULL, 'user'=>NULL, 'pass'=>NULL, 'host'=>NULL, 'port'=>NULL, 'path'=>NULL, 'query'=>NULL, 'fragment'=>NULL), $parsed);

	}

	/**
	* Protocolo da URL.
	* @access private
	* @var string Protocolo da URL.
	*/
	private $__scheme = 'http';

	/**
	* Armazena o domínio usado na URL.
	* @access private
	* @var string Domínio da URL.
	*/
	private $__host;

	/**
	* Caminho para o recurso solicitado.
	* @access private
	* @var string Caminho da URL.
	*/
	private $__path;

	/**
	* Ancora.
	* @access private
	* @var string Ancora da URL.
	*/
	private $__fragment;

	/**
	* Armazena os parâmetros da URL.
	* @access private
	* @var string Parâmetros da URL.
	*/
	private $__query;

	/**
	* Porta de comunicação.
	* @access private
	* @var string Porta da URL.
	*/
	private $__port;

	/**
	* Armazena o usuário do recurso.
	* @access private
	* @var string Usuário da URL.
	*/
	private $__user;

	/**
	* Armazena a senha do recurso.
	* @access private
	* @var string Senha da URL.
	*/
	private $__pass;

	/**
	* Método construtor da classe.
	* Recebe como parametro uma string
	* URL e "parseia" essa string
	* identicando as partes da URL
	* e alimentando os atributos do
	* objeto.
	*
	* @access public
	* @param string $url URL.
	* @return void
	* @see \Habilis\Url::parse()
	*/
	public function __construct($url=NULL)
	{

		if( $url )
		{
	
			$parsed = self::parse((string)$url);
	
			foreach($parsed as $k => $v)
			{

				$method = 'set' . ucfirst(strtolower($k));
		
				$this->{$method}($v);

			}
	
		}
		
	}

	/**
	* Retorna o objeto como uma URL.
	*
	* @access public
	* @return string URL gerada baseando-se nos atributos do objeto.
	* @see \Habilis\Url::asString()
	*/
	public function __toString()
	{

		return $this->asString();
	
	}

	/**
	* Retorna uma string URL
	* usando os atributos do objeto.
	*
	* @access public
	* @param bool $filter Remove certos caracteres da URL.
	* @return string URL gerada baseando-se nos atributos do objeto.
	* @see \Habilis\Url::getPathQuery()
	*/
	public function asString($filter=TRUE)
	{

		$scheme = ( $this->__scheme ) ? $this->__scheme . '://' : '';
	
		$user = ( $this->__user ) ? $this->__user : '';
	
		$pass = ( $this->__pass ) ? ':' . $this->__pass  : '';
	
		$pass = ( $user || $pass ) ? "$pass@" : '';
	
		$host = ( $this->__host ) ? $this->__host : '';
	
		$port = ( $this->__port ) ? ':' . $this->__port : '';

		// $pathquery = $this->getPathQuery();

		$path = $this->getPath();

		if( $path && strlen($host)>0 )
		{
			$path = '/' . $path;
		}

		$query = $this->getQuery();

		if( $query )
		{
			$query = '?' . $query;
		}
	
		$fragment = ( $this->__fragment ) ? '#' . $this->__fragment : '';
	
		$url = $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
	
		if( $filter )
		{
		
			$trans_table = array(
				'['=>'&#91;',
				']'=>'&#93;',
				'$'=>'&#36;',
				'('=>'&#40;',
				')'=>'&#41;',
				'%28'=>'&#40;',
				'%29'=>'&#41;'
				);

			return str_replace(array_keys($trans_table), $trans_table, $url);

		}
	
		return $url;	

	}

	
	/**
	 * Método valida o caminho definido
	 * na URL. Basicamente o que ele faz
	 * eh dizer que um caminho que possua
	 * duas barras seguindas não eh valido.
	 * Ex.: /meu//caminho
	 */
	public function validatePath($path=NULL)
	{

		if( $path===NULL )
		{
			$path = $this->__path;
		}


		if( ! is_array($path) )
		{
			$path = explode("/", trim($path, "/"));
		}

		foreach($path as $part)
		{
			if( empty($part) )
			{
				return FALSE;
			}
		}

		return TRUE;

	}

	/**
	* Retorna o caminho da URL
	* incluindo a chamada (os
	* parâmetros) da URL em
	* formato string.
	* 
	* @access public
	* @return string Caminho junto com a chamada.
	* @see \Habilis\Url::getQuery()
	* @see \Habilis\Url::getPath()
	*/
	public function getPathQuery()
	{
	
		$pathquery = $this->getPath();
	
		$query = $this->getQuery();
	
		if( $query )
		{
			$pathquery .= '?' . $query;
		}
	
		return $pathquery;

	}

	/**
	 * Define o caminho na URL.
	 *
	 * @access public
	 * @param $path
	 * @return $this
	 * @see \Habilis\Url::$__path
	 * @throws \Exception
	 */
	public function setPath($path)
	{

		if( ! $this->validatePath($path) )
		{
			throw new \Exception("Internal error: path validation failed.");
		}

		if( ! empty($path) )
		{

			if( is_array($path) )
			{
				$path = implode('/', $path); // $path = '/' . implode('/', $path);
			}

			$this->__path = (string)$path;

		}

		return $this;
	
	}

	/**
	* Retorna o caminho da URL.
	*
	* @access public
	* @return string Caminho da URL.
	* @see \Habilis\Url::getPathAsArray()
	*/
	public function getPath()
	{
	
		$array = $this->getPathAsArray();

		return implode('/', $array); // return '/' . implode('/', $array);

	}

	/**
	* Pega o caminho como array.
	*
	* @access public
	* @return array Caminho como array.
	* @see \Habilis\Url::getPathAsArray()
	*/
	public function getPathAsArray()
	{

		$array = array();

		$parts = explode('/', trim($this->__path, '/'));

		foreach((array)$parts as $segment)
		{
			if( $segment )
			{
				$array[] = $segment;
			}
			else
			{
				break;
			}
		}

		return $array;
	
	}

	/**
	 * Seta uma porta para a URL.
	 *
	 * @param $port
	 * @return $this
	 * @throws \Exception
	 */
	public function setPort($port)
	{

		$this->__port = (int)$port;

		return $this;

	}

	/**
	* Pega a porta. Se o parâmetro for
	* verdadeiro, tenta retornar a
	* porta baseando-se no esquema.
	*
	* @access public
	* @param bool $use_default [optional] default:FALSE Retorna a porta baseando no esquema.
	* @return integer Porta da URL.
	* @see \Habilis\Url::$__port
	* @see \Habilis\Url::$__scheme
	*/
	public function getPort($use_default=FALSE)
	{
	
		$port = ( $this->__port ) ? (int)$this->__port : FALSE;
	
		if( ! $port && $use_default )
		{
			if ($this->__scheme == 'http')
			{
				$port = 80;
			}
			elseif ($this->__scheme == 'https')
			{
				$port = 443;
			}
		}
	
		return $port;

	}

	/**
	* Define o protocolo de transmissão
	* utilizado na URL.
	*
	* @access public
	* @param string $scheme Protocolo da URL (http, ftp, etc.).
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__scheme
	*/
	public function setScheme($scheme)
	{

		$this->__scheme = $scheme;

		return TRUE;
	
	}

	/**
	* Pega o esquema.
	*
	* @access public
	* @return string Protocolo da URL.
	* @see \Habilis\Url::$__scheme
	*/
	public function getScheme()
	{

		return $this->__scheme;
	
	}

	/**
	* Seta um domínio/IP para a URL.
	*
	* @access public
	* @param string $host Domínio ou endereço IP.
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__host
	*/
	public function setHost($host)
	{

		$this->__host = $host;

		return TRUE;
	
	}

	/**
	* Pega o nome de domínio/IP.
	*
	* @access public
	* @return string Dóminio/IP da URL.
	* @see \Habilis\Url::$__host
	*/
	public function getHost()
	{

		return ( $this->__host ) ? $this->__host : FALSE;

	}

	/**
	* Seta um usuário para a URL.
	*
	* @access public
	* @param string $user Usuário da URL.
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__user
	*/
	public function setUser($user)
	{
		
		$this->__user = $user;
		
		return TRUE;

	}

	/**
	* Pega o usuário ou retorna falso.
	*
	* @access public
	* @return mixed Dóminio/IP da URL ou falso caso não esteja definido.
	* @see \Habilis\Url::$__user
	*/
	public function getUser()
	{
		return ( $this->__user ) ? $this->__user : FALSE;
	}

	/**
	* Seta uma senha para a URL.
	*
	* @access public
	* @param string $pass Senha da URL.
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__pass
	*/
	public function setPass($pass)
	{
		$this->__pass = $pass;

		return TRUE;
	}

	/**
	* Pega a senha ou retorna falso.
	*
	* @access public
	* @return mixed Senha da URL ou falso caso não esteja definida.
	* @see \Habilis\Url::$__pass
	*/
	public function getPass()
	{
		return ( $this->__pass ) ? $this->__pass : FALSE;
	}

	/**
	* Seta um fragmento para a URL.
	*
	* @access public
	* @param string $fragment Âncora da URL.
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__fragment
	*/
	public function setFragment($fragment)
	{
		$this->__fragment = $fragment;

		return TRUE;
	}

	/**
	* Retorna a âncora da URL (tudo
	* que vem após #) ou retorna falso.
	*
	* @access public
	* @return mixed Âncora da URL ou falso caso não esteja definida.
	* @see \Habilis\Url::$__fragment
	*/
	public function getFragment()
	{
		return ( $this->__fragment ) ? $this->__fragment : FALSE;
	}

	/**
	* Adiciona chamada a URL.
	*
	* @access public
	* @param mixed $query Parametros da URL como string ou como um dicionário (array associativo).
	* @return bool Retorna sempre verdadeiro.
	* @see \Habilis\Url::$__query
	*/
	public function setQuery($query)
	{
	
		if( is_array($query) )
		{
			$query = http_build_query($query, '', '&');
		}

		$this->__query = $query;

		return TRUE;

	}

	/**
	* Pega a parâmetros da URL
	* ou retorna falso.
	*
	* @access public
	* @return mixed Parâmetros da URL ou falso caso não tenha sido setada.
	* @see \Habilis\Url::$__query
	*/
	public function getQuery()
	{
		return ( $this->__query ) ? (string)$this->__query : FALSE;
	}

	/**
	* Pega a chamada no formato
	* de um array associativo.
	*
	* @access public
	* @return array Parâmentros da URL como dicionário (array associativo).
	* @see \Habilis\Url::$__query
	* @see \Habilis\Url::$__queryToArray()
	*/
	public function getQueryAsArray()
	{

		return $this->__queryToArray($this->__query);

	}

	/**
	* Pega o valor de determinado
	* parâmetro definido na URL.
	*
	* @access public
	* @param string Nome do parâmetro que se quer recuperar o valor.
	* @return array|false Parâmentro da URL ou falso caso o parâmetro não exista.
	* @see \Habilis\Url::$getQueryAsArray()
	*/
	public function getAttr($key)
	{
	
		$query_array = $this->getQueryAsArray();
	
		return @ $query_array[ $key ];
	
	}

	/**
	* @access private
	* @param string $query Parâmetros de URL em formato string (ex.: 'nome=valor1&nome2=valor2').
	* @return array Parâmetro da URL organizado em um dicionário (array associativo) onde a chave é o parâmetro e o conteúdo seu valor.
	*/
	private function __queryToArray($query)
	{
	
		parse_str((string)$query, $parsed);
	
		foreach((array)$parsed as $k => $v)
		{
			if(preg_match("/^\[(.*?)\]$/", $v, $match))
			{
				$parsed[ $k ] = $this->__queryToArray($match[1]);
			}
		
		}
	
		return $parsed;

	}

	/**
	 * Valida a URL.
	 * TODO: Implementar esse método.
	 */
	public function valid()
	{

		return $this->validatePath();

	}

}
