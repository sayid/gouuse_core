<?php

namespace GouuseCore\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\StatementPrepared;
use Event;

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
	}
}
