<?php

namespace SlavaWins\AuthSms\Providers;

use Illuminate\Support\ServiceProvider;

class AuthSmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //$loader = \Illuminate\Foundation\AliasLoader::getInstance();
       // $loader->alias("FElement");
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'authsms');

        $this->publishes([
            __DIR__.'/../resources/js' => public_path('js/authsms'),
        ], 'public');
    }
}
