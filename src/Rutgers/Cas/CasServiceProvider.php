<?php

namespace Rutgers\Cas;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class CasServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Publish CAS configuration and CAS views
         */
        
        $this->publishes([
            __DIR__ . '/../../config/cas.php' => config_path('cas.php'),
            __DIR__ . '/../../views' => resource_path('views/vendor/laravel-cas'),
        ], 'laravel-cas');

        /**
         * Publish modified Laravel auth configuration
         * which has been modified to setup CAS auth guard
         * NEEDS --force FLAG TO PUBLISH
         */

        $this->publishes([
            __DIR__ . '/../../config/auth.php' => config_path('auth.php'),
        ], 'laravel-cas-authconfig');

        /**
         * Publish CAS login/logout routes
         */

        $this->loadRoutesFrom(__DIR__.'/../../routes/cas_routes.php');

        /**
         * Load CAS views
         */
        $this->loadViewsFrom(__DIR__.'/../../views', 'laravel-cas');

        /**
         * Extend authentication system with CAS driver, link to CAS auth guard
         */

        Auth::extend('cas', function($app, $name, array $config) {
            
            $guard = new CasGuard('cas', Auth::createUserProvider($config['provider']), app('session.store'));

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }
    
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cas', function () {
            return new CasManager( config('cas') );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('cas');
    }

}