<?php

namespace GouuseCore\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use GouuseCore\Libraries\CodeLib;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
			AuthorizationException::class,
			HttpException::class,
			ModelNotFoundException::class,
			ValidationException::class,
	];
	
	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		parent::report($e);
	}
	
	
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if (env('APP_DEBUG') == true) {
			$data = $e->__toString();
		} else {
			$data = '';
		}
		if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
			return response(['code'=>CodeLib::REQUEST_NOT_FOUND,'exception'=>$data], 404);
		} else if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
			return response(['code'=>CodeLib::REQUEST_METHOD_ERROR,'exception'=>$data], 405);
		}
		return response(['code'=>CodeLib::HTTP_ERROR,'exception'=>$data], 500);
		return parent::render($request, $e);
	}
}
