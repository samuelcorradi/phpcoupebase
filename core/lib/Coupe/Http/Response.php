<?php

namespace Coupe\HTTP;

/*
* Objeto de contendo informações
* de uma resposta HTTP.
* Recebe a versao do protocolo
* HTTP como parametro, o status
* da resposta e os dados de
* cabeçalho.
* Possui um metodo para definir
* o corpo da resposta.
*/
class Response
{

	/**
	 * Versões.
	 */

	const HTTP_ENV = 0;

	const HTTP_10 = "HTTP/1.0";

	const HTTP_11 = "HTTP/1.1";

	const HTTP_2 = "HTTP/2";

	/**
	 * Cabeçalho da resposta.
	 */
	public $header = array();

	/**
	* Frase customizada a ser adiciona a
	* resposta.
	*
	* @var string Frase customizada.
	*/
	public $statusText;

	/**
	 * Versão do HTTP utilizado na
	 * comunicação.
	 */
	public $_version;

	/**
	* Status da resposta enviada.
	*
	* @var int Código do status.
	*/
	protected $statusCode;

	/**
	* Corpo da resposta a ser enviada.
	*
	* @var string Frase customizada.
	*/
	protected $_body = "";

	/**
	* Configurações de frases que são
	* adicionados ao cabeçalho da resposta
	* de acordo com o status da mesma.
	*
	* @var array Frases associadas ao seu status.
	*/
	public static $status = array(
	100 => 'Continue',
	101 => 'Switching Protocols',
	103 => 'Checkpoint',
	200 => 'OK',
	201 => 'Created',
	202 => 'Accepted',
	203 => 'Non-Authoritative Information',
	204 => 'No Content',
	205 => 'Reset Content',
	206 => 'Partial Content',
	300 => 'Multiple Choices',
	301 => 'Moved Permanently',
	302 => 'Found',
	303 => 'See Other',
	304 => 'Not Modified',
	306 => 'Switch Proxy',
	307 => 'Temporary Redirect',
	308 => 'Resume Incomplete',
	400 => 'Bad Request',
	401 => 'Unauthorized',
	402 => 'Payment Required',
	403 => 'Forbidden',
	404 => 'Not Found',
	405 => 'Method Not Allowed',
	406 => 'Not Acceptable',
	407 => 'Proxy Authentication Required',
	408 => 'Request Timeout',
	409 => 'Conflict',
	410 => 'Gone',
	411 => 'Length Required',
	412 => 'Precondition Failed',
	413 => 'Request Entity Too Large',
	414 => 'Request-URI Too Long',
	415 => 'Unsupported Media Type',
	416 => 'Requested Range Not Satisfiable',
	417 => 'Expectation Failed',
	500 => 'Internal Server Error',
	501 => 'Not Implemented',
	502 => 'Bad Gateway',
	503 => 'Service Unavailable',
	504 => 'Gateway Timeout',
	505 => 'HTTP Version Not Supported',
	511 => 'Network Authentication Required',
	599 => 'Network Connect Timeout Error'
	);

	/**
	 * O protocolo HTTP/1.1 eh a versão
	 * padrão do objeto.
	 */
	public function __construct($protocol="HTTP/1.1", $status=200, Array $header=array())
	{

		$this->setVersion($protocol);

		$this->setStatus($status);

		foreach ($header as $k => $v)
		{
			$this->setHeader($k, $v);
		}

	}


	/*
	* Define a versão do HTTP a ser
	* utilizada na comunicação.
	* Versões possiveis ateh esta
	* versão são 1.0, 1.1 e 2.
	* Caso se queira utilizar a versão
	* do servidor que esteja rodando a
	* aplicação passa-se 0 ou a constante
	* self::HTTP_ENV.
	*/
	public function setVersion($v)
	{

		if($v===static::HTTP_ENV)
		{
			$v = $_SERVER['SERVER_PROTOCOL'];
		}
		
		if(in_array($v, array(static::HTTP_10, static::HTTP_11, static::HTTP_2)))
		{
			$this->_version = $v;
		}
		else
		{
			throw new \Exception("Invalid HTTP version.", 1);
		}

		return $this;

	}

	/*
	* Retorna a versão do HTTP.
	*/
	public function getVersion()
	{

		return $this->_version;

	}

	/**
	* Define o status da resposta.
	*/
	public function setStatus($code, $text=null)
	{

		$this->_code = (int) $code;

		if ( ! $this->isValidStatus() )
		{
			throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
		}

		$this->setStatusText($text);

		return $this;

	}

	/**
	* Pega o status code.
	*/
	public function getStatus()
	{

		return $this->_code;

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
	* Rederiza a linha de status da resposta.
	*/
	public function renderStatusLine()
	{

		$s = sprintf(
		'HTTP/%s %d %s',
		$this->getVersion(),
		$this->getCode(),
		$this->getPhrase()
		);

		return trim($s);

	}

	public function setBody($b)
	{

		$this->_body = "";

		return $this->appendBody($b);

	}

	/**
	* Define o corpo da resposta HTTP.
	*
	* @return string Corpo da resposta HTTP.
	*/
	public function appendBody($b)
	{

		if (is_string($b) || is_numeric($b) || is_callable(array($b, '__toString')))
		{

			$this->_body = (string)$b;

			return $this;

		}

		throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
        
	}

	/**
	* Retorna o corpo da resposta HTTP.
	*
	* @return string Corpo da resposta HTTP.
	*/
	public function getBody()
	{

		return $this->_body;

	}

	/**
	* Imprime o objeto em formato
	* de string de modo que revele
	* o código do cabeçalho e corpo.
	*/
	public function __toString()
	{

		$h = '';

		foreach($this->header as $k => $v)
		{
			$h .= $k . "=" . $v . "\r\n";
		}

		return $this->renderStatusLine() . "\r\n" . $h . "\r\n" . $this->getBody();

	}

	/**
	 * Define uma frase para o status no
	 * cabecalho da resposta HTTP.
	 * Se null for passado, tenta utilizar
	 * o texto na tabela de status. Se false
	 * for passado atribui um texto vazio.
	 * Se for passado qualquer outro tipo
	 * faz o cast para string.
	 */
	public function setStatusText($t)
	{

		if ($t===null)
		{
			$this->statusText = isset(static::$status[$this->statusCode]) ? static::$status[$this->statusCode] : 'unknown status';
		}
		elseif (false === $text)
		{
			$this->statusText = '';
		}
		else
		{
			$this->statusText = (string)$t;
		}

		return $this;

	}

	/**
    * Pega a frase de status que faz parte
    * do cabeçalho de resposta.
    */
	public function getStatusText()
	{

		return $this->statusText;

	}

	/**
	 * Valida uma linha de status da resposta.
	 */
	protected function isValidStatusLine($line)
	{

		if( $this->isValidStatus() && preg_match('/^HTTP\/(?P<version>1\.[01]) (?P<status>\d{3})(?:[ ]+(?P<reason>.*))?$/', $line, $m))
		{
			return true;
		}

		return false;

	}

	/**
     * Is valid status code?
     *
     * @return bool
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function isValidStatus()
    {

        return 100 <= $this->statusCode || $this->statusCode < 600;

    }

	/**
	* Cria uma objeto do tipo Response através
	* de uma string que represente uma resposta
	* HTTP.
	* $response = Response::fromString(<<<EOS
	* HTTP/1.0 200 OK
	* HeaderField1: header-field-value
	* HeaderField2: header-field-value2
	* <html>
	* <body>
	*	Hello World
	* </body>
	* </html>
	* EOS);
	*
	* @return \Coupe\Http\Response
	*/
	public static function fromString($s)
	{

		$l = explode("\r\n", $s);

		if ( ! is_array($l) || count($l)==1)
		{
			$l = explode("\n", $s);
		}

		$firstl = array_shift($l);

		$resp = new \Coupe\Html\Response();

		$m = array();

		if ( ! $this->isValidStatusLine($firstl) )
		{
			throw new Exception('A valid response status line was not found in the provided string');
		}

		$resp->setVersion(preg_replace('/[^0-9]+/', '', $m['version']));

		try
		{
			$resp->setStatus($m['status'], (isset($m['reason'])) ? $m['reason'] : false);
		}
		catch(\Exception $e)
		{
			throw new \Exception($e->getMessage(), 1);
		}

		if (count($l)==0)
		{
			return $resp;
		}

		$body = array();

		$is_header = TRUE;

		foreach ($l as $line)
		{

			if ($is_header && $line=="")
			{
				$is_header = FALSE; continue;
			}

			if ($is_header)
			{
				// $resp->getHeader()->add($line); // $headers[] = $line;
			}
			else
			{
				$body[] = $line;
			}

		}

		if ( ! empty($body) )
		{
			$resp->setBody(implode("\r\n", $body));
		}

		return $resp;

	}

}

?>