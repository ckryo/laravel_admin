<?php

namespace Ckryo\Laravel\Admin;

use Ckryo\Laravel\Http\ErrorCode;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot(ErrorCode $errorCode)
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $errCodes = require __DIR__.'/../config/errCode.php';
        foreach ($errCodes as $model => $codes) {
            $errorCode->regist($model, $codes);
        }
    }

    public function register()
    {
        $this->app->singleton('auth', function ($app) {
            return new Auth($app['request']);
        });

        $this->app->bind(Auth::class, function ($app) {
            return $app->make('auth');
        });
    }

}
