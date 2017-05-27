<?php
namespace GouuseCore\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class QueryListener
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  QueryExecuted  $event
	 * @return void
	 */
	public function handle(QueryExecuted $event)
	{
		//
		$sql = str_replace("?", "'%s'", $event->sql);
		$log = vsprintf($sql, $event->bindings);
		
		Log::info($log);
	}
}
