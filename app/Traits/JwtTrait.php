<?php

namespace App\Traits;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use App\Exceptions\JwtExceptions;
use Illuminate\Support\Facades\Log;


trait JwtTrait {

  public static function signAccessToken(string $username, string $_id, array $roles) {

      $payload = ['userInfo' => [
      'username' => $username,
      'roles' => $roles,
      '_id' => $_id,
      ],
      'exp' => time() + 900
      ];

      $accessToken = JWT::encode($payload, Config::get('app.accessTokenKey'), 'HS256');

      if ($accessToken) return $accessToken;
      else return null;
  }

  public static function signRefreshToken(string $username) {

      $payload = [
      'username' => $username,
      'exp' => time() + 86400
      ];

      $refreshToken = JWT::encode($payload, Config::get('app.refreshTokenKey'), 'HS256');

      if ($refreshToken) return $refreshToken;
      else return null;
  }

  public static function verifyAccessToken(string $accessToken) {
    try {
      return JWT::decode($accessToken, new Key(Config::get('app.accessTokenKey'), 'HS256'));
    } catch (SignatureInvalidException $e) {
      Log::channel('jwtExceptions')->info($e->getMessage());
      return null;
    } catch (\Exception $e) {
      Log::channel('jwtExceptions')->info($e->getMessage());
      return null;
    }
  }

  public static function verifyRefreshToken(string $refreshToken) {
    try {
      return JWT::decode($refreshToken, new Key(Config::get('app.refreshTokenKey'), 'HS256'));
    } catch (SignatureInvalidException $e) {
      Log::channel('jwtExceptions')->info($e->getMessage());
      return null;
    } catch (\Exception $e) {
      Log::channel('jwtExceptions')->info($e->getMessage());
      return null;
    }
  }
}
