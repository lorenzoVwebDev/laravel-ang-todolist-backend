<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use App\Http\Controllers\TasksController;
use Illuminate\Http\Request;

class ApisProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TasksController::class, function (Application $app) {
            return new TasksController($app->make(Request::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
