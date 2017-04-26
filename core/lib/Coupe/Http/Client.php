<?php

namespace Coupe\Http;

class Client
{

	/**
	 * Dados a serem submetidos.
	 */
	public $data;

	/**
	* O status code significa um erro do cliente?
	*
	* @return bool
	*/
	public function isClientError()
	{

		$c = $this->getStatusCode();

		return ($c<500 && $c>=400);

	}

	/**
	* 
	* @return bool
	*/
	public function isForbidden()
	{

		return (403===$this->getStatusCode());

	}

	/**
	* @return bool
	*/
	public function isInformational()
	{

		$c = $this->getStatusCode();

		return ($c>=100 && $c<200);

	}

	/**
	* Status indica que o conteúdo não
	* foi encontrado?
	*
	* @return bool
	*/
	public function isNotFound()
	{

		return (404===$this->getStatusCode());

	}

	/**
	* Status indica uma resposta normal?
	*
	* @return bool
	*/
	public function isOk()
	{

		return (200===$this->getStatusCode());

	}

	/**
	* Status indica um erro no servidor?
	*
	* @return bool
	*/
	public function isServerError()
	{

		$c = $this->getStatusCode();

		return (500<=$c && 600>$c);

	}

	/**
	* É um redirecionamento?
	*
	* @return bool
	*/
	public function isRedirect()
	{

		$c = $this->getStatusCode();

		return (300<=$c && 400>$c);

	}

	/**
	*
	* @return bool
	*/
	public function isSuccess()
	{

		$c = $this->getStatusCode();

		return (200 <= $c && 300 > $c);

	}

}

?>