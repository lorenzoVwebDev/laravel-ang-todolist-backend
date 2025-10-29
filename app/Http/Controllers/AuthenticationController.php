<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
//Services
use Illuminate\Support\Str;
//Models
use App\Models\UsersModel;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {

    }
    public function signUp() {
        @['username' => $username, 'email' => $email, 'password' => $password] = $this->request->all();

        if (@!$username || @!$email || @!$password) return Response::json(["response" => "missing-credentials"], 401);
    }
}
