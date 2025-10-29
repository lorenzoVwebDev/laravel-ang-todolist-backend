<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\JwtTrait;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Config;
use App\Models\UsersModel;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Log;

class VerifyJwt/*  extends EncryptCookies */ {

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->header(Config::get('app.accessTokenHeader'));
        $refreshToken = $request->cookie('refreshToken');

        if (!$refreshToken) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 401);
        if (!$accessToken && !$refreshToken) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 401);
        $accessTokenArray = explode(' ', $accessToken);
        if (explode(' ', $accessToken)[0] === 'Bearer') {
          $result = JwtTrait::verifyAccessToken($accessTokenArray[1]);
          if ($result) {
            //Defining request roles and username
            $request->merge([
              'user' => $result->userInfo->username,
              //Parsing decode token roles in an associative array
              'roles' => json_decode(json_encode($result->userInfo->roles), true)
            ]);
            $user = $result->userInfo->username;
            $request->setUserResolver(function () use ($user) {
              return $user;
            });
            return $next($request);
          } else {
            try {
              if (!isset($refreshToken)) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 401);
              $user = UsersModel::get()->where('refreshToken', $refreshToken);
              if (!isset($user->toArray()[0]['username'])) return ResponseFacade::json(['response' => 'jwt-not-found'], 403);
              $userArray = $user->toArray()[0];
              $result = JwtTrait::verifyRefreshToken($refreshToken);
              if (!$result || ($result->username != $userArray['username'])) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 403);
              $accessToken = JwtTrait::signAccessToken($userArray['username'],$userArray['_id'], json_decode($userArray['roles'], true));
              if (!$accessToken) return ResponseFacade::noContent(500);
              return ResponseFacade::noContent(201, [
                Config::get('app.accessTokenHeader') => $accessToken
              ]);
            } catch (\Exception $e) {
              Log::error($e->getMessage());
              return ResponseFacade::noContent(500);
            }
          }
        } else {
          try {
            if (!isset($refreshToken)) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 401);
            $user = UsersModel::get()->where('refreshToken', $refreshToken);
            if (!isset($user->toArray()[0]['username'])) return ResponseFacade::json(['response' => 'jwt-not-found'], 403);
            $userArray = $user->toArray()[0];
            $result = JwtTrait::verifyRefreshToken($refreshToken);
            if (!$result || ($result->username != $userArray['username'])) return ResponseFacade::json(['response' => 'jwt-unauthorized'], 403);
            $accessToken = JwtTrait::signAccessToken($userArray['username'],$userArray['_id'], json_decode($userArray['roles'], true));
            if (!$accessToken) return ResponseFacade::noContent(500);
            return ResponseFacade::noContent(201, [
              Config::get('app.accessTokenHeader') => $accessToken
            ]);
          } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ResponseFacade::noContent(500);
          }
        }
    }
}
