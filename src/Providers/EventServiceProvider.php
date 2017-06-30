<?php

namespace GouuseCore\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\StatementPrepared;
use Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;

class EventServiceProvider extends ServiceProvider {
	/**
	 * The event listener mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [ 
			'App\Events\SomeEvent' => [ 
					'App\Listeners\EventListener' 
			],
			'Illuminate\Database\Events\QueryExecuted' => [ 
					'GouuseCore\Listeners\QueryListener' 
			] 
	];
	public function boot() {
		Event::listen ( StatementPrepared::class, function ($event) {
			$event->statement->setFetchMode(\PDO::FETCH_ASSOC);
		});
		if (env('APP_DEBUG') == true) {
			Event::listen ( QueryExecuted::class, function ($event) {
				$sql = str_replace("?", "'%s'", $event->sql);
				$log = vsprintf($sql, $event->bindings);
				Log::debug($log);
			});
		}
	}
	
}
