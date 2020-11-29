<?php

namespace DomainParser;

use Illuminate\Support\ServiceProvider;

class DomainParserServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ( $this->app->runningInConsole() ) $this->bootForConsole();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/domainparser.php', 'domainparser');

        $this->app->singleton('domainparser', function ($app) {
            return new DomainParser;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['domainparser'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__.'/../config/domainparser.php' => config_path('domainparser.php'),
        ], 'domainparser.config');
    }
}
