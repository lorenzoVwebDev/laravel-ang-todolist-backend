<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TasksController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
//Middlewares
use App\Http\Middleware\VerifyJwt;
use App\Http\Middleware\AvatarCreator;
use App\Traits\JwtTrait;
use App\Models\TasksModel;
use Illuminate\Database\Eloquent\Factories\Sequence;
//controllers
use App\Http\Controllers\AuthenticationController;


Route::get('/', function (Request $request) {
return 'hello';
});

Route::get('/refreshtoken', function (Request $request) {
    $refreshToken = JwtTrait::signRefreshToken('lorenzo');
    return ResponseFacade::json(['response' => "signed-cookie"], 200)->cookie('refreshToken', $refreshToken, time() + 86400, '/', '', false, false, false, 'Lax');
});

Route::match(['get', 'post', 'delete', 'put'], '/tasks/{id?}', function (Application $app, Request $request, ?string $id = null) {
    $pathArray = explode('/',$request->path());

    if (count($pathArray) === 2 && ($pathArray[1] === 'searchtasks' || $pathArray[1] === 'filtertasks')) {
        $tasksController = $app->make(TasksController::class);

        switch ($request->method()) {
          case "GET": {
            if ($pathArray[1] === 'searchtasks') return $tasksController->searchTask();
            if ($pathArray[1] === 'filtertasks') return $tasksController->filterTask();
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
                    break;
                }
                case "POST": {
                    return $tasksController->postTask();
                    break;
                }
                case "PUT": {
                    return $tasksController->updateTask();
                    break;
                }
                default: {
                    return abort(400);
                }
            }
        } else if (preg_match('/^\d+$/', $pathArray[1])) {
            switch ($request->method()) {
                case "DELETE": {
                    return $tasksController->deleteTask($id);
                    break;
                }
                default: {
                    return abort(400);
                }
            }
        } else {

            if (!preg_match('/^\d+$/', $pathArray[1])) return Response::json(["response" => "invalid-request"]);
            //return the default resources/views/errors/404.blade.php
            return abort(404);
        }
    } else {
        return abort(404);
    }
})->middleware(VerifyJwt::class);

Route::prefix("authentication")->group(function () {

    Route::post('/signup', function (Application $app, AuthenticationController $authController) {

        return $authController->signUp();
    })->middleware(AvatarCreator::class);

    Route::post('/signin', function (Application $app, AuthenticationController $authController) {
        return $authController->signIn();
    });

    Route::delete('/logout', function (Application $app, AuthenticationController $authController) {
        return $authController->logOut();
    })->middleware(VerifyJwt::class);

    Route::post("/changepwr", function (Application $app, AuthenticationController $authController) {
        return $authController->changePwd();
    });
});
