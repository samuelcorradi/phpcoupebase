<?php

/**
* Interface Middleware
*
* Interface para filtros HTTP.
*
* @version 0.1
* @package Coupe\Http\Middleware
* @subpackage Filtering
* @author Samuel Corradi <falecom@samuelcorradi.com.br>
* @copyright Copyright (c) 2012, habilis.com.br
* @license http://creativecommons.org/licenses/by-nd/3.0 Creative Commons BY-ND
* @example http://www.habilis.com.br/documentation/classes/coupe/html/middleware
* @see \Habilis\Http
*/

namespace Coupe;

interface IMiddleware
{

	public function __invoke();

}
