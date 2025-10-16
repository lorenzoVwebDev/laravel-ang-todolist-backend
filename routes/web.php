<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TasksController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post', 'delete', 'put'], '/tasks/{id?}', function (Application $app, Request $request) {
    $tasksController = $app->make(TasksController::class);
    switch ($request->method()) {
        case "GET": {
            return $tasksController->getTask();
        }
    }
});