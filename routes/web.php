<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TasksController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Middleware\VerifyJwt;
use App\Traits\JwtTrait;
use Illuminate\Support\Facades\Response as ResponseFacade;

Route::get('/', function (Request $request) {
/*     return view('welcome'); */
})->middleware(VerifyJwt::class);

Route::get('/refreshtoken', function (Request $request) {
    $refreshToken = JwtTrait::signRefreshToken('lorenzo');
    return ResponseFacade::json(['response' => "signed-cookie"], 200)->cookie('refreshToken', $refreshToken, time() + 86400, '/', '', false, false, false, 'Lax');
});

Route::match(['get', 'post', 'delete', 'put'], '/tasks/{id?}', function (Application $app, Request $request) {
    $pathArray = explode('/',$request->path());
    
    if (count($pathArray) === 2 && ($pathArray[1] === 'searchtasks' || $pathArray[1] === 'filtertasks')) {
        $tasksController = $app->make(TasksController::class);

        switch ($request->method()) {
          case "GET": {
            return $tasksController->searchTask();
          }
          default: {
            return abort(403);
          }
        }
    } else if (count($pathArray) <= 2 && $pathArray[0] === 'tasks') {
        $tasksController = $app->make(TasksController::class);

        if (count($pathArray) === 1) {
            switch ($request->method()) {
                case "GET": {
                    return $tasksController->getTask();
                }
                
            }
        } else if (preg_match('/^\d+$/', $pathArray[1])) {

        } else {
            //return the default resources/views/errors/404.blade.php 
            return abort(404);
        }
    } else {
        return abort(404);
    }
});