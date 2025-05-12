<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\CustomDatabaseSessionHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        View::composer('layouts.app', function($view){
            if(Auth::check()){
                $view->with('userName', Auth::user()->name);
                $view->with('emailAddress', Auth::user()->email);
            } else{
                $view->with('userName', null);
                $view->with('emailAddress', null);
            }
        });

        Session::extend('custom-database', function ($app) {
            $table = $app['config']['session.table'];
            $connection = $app['db']->connection();
            $lifetime = $app['config']['session.lifetime'];
            return new CustomDatabaseSessionHandler($connection, $table, $lifetime, $app);
        });
    }
}
