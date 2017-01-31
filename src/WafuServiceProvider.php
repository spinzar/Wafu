<?php namespace Sukohi\Wafu;

use Illuminate\Support\ServiceProvider;

class WafuServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

    public function register()
    {
        $this->app->singleton('wafu', function(){

            return new Wafu;

        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return array('wafu');
	}

}
