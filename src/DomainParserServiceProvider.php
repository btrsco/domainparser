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
        $this->mergeConfigFrom(__DIR__.'/../config/domain-parser.php', 'domain-parser');

        $this->app->singleton('domain-parser', function ($app) {
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
        return ['domain-parser'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__.'/../config/domain-parser.php' => config_path('domain-parser.php'),
        ], 'domain-parser.config');
    }
}
