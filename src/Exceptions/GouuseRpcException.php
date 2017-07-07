<?php 
namespace GouuseCore\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class GouuseRpcException extends HttpException
{
	public function __construct($message = null, \Exception $previous = null, $code = 0)
	{
		parent::__construct(404, $message, $previous, array(), $code);
	}
  
}  