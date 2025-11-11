<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
//Services
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\JwtTrait;
use Illuminate\Support\Facades\Cookie;
//Models
use App\Models\UsersModel;

class AuthenticationController extends Controller
{
    public function __construct(public Request $request) {}

    public function signUp() {
        @['username' => $username, 'email' => $email, 'password' => $password] = $this->request->all();

        if (@!$username || @!$email || @!$password) return Response::json(["response" => "missing-credentials"], 401);

        $username = strip_tags($username);
        if (!(preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", strip_tags($email)) === 1)) return Response::json(["response" => "ivalid-email"], 400);

        $email = strip_tags($email);
        if (!(preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()])[A-Za-z\d!@#$%^&*()]{8,}$/", strip_tags($password)))) return Response::json(["response" => "invalid-password"], 400);
        $password = strip_tags($password);
        $newUser = [
            "username" => $username,
            "email" => $email,
            "password" => $password
        ];

        try {
            if (count(UsersModel::where("username", $username)->get())>0||count(UsersModel::where("email", $email)->get())>0) return Response::json(["response"=>"user-duplicated"], 409);

            $avatarUuid = $this->request->input("tempAvatarUuid");

            if (@$avatarUuid) {
                $avatarFile = Storage::get("avatars/".$avatarUuid);
                $newUser["avatar"] = $avatarFile;
            }

            $hexId = substr(md5(rand()), 0, 26);
            while (count(UsersModel::where('_id', $hexId)->get()) > 0) {
                $hexId = substr(md5(rand()), 0, 26);
            }
            $newUser["_id"] = $hexId;
            $newUser["password"] = password_hash($newUser["password"], PASSWORD_BCRYPT, ["cost" => 10]);
            $newUser["oldPasswords"] = json_encode([$newUser["password"]]);
            $newUser["dateStamp"] = strtotime("+1 month");
            $newUser["roles"] = json_encode(["Users" => 2001]);

            UsersModel::insert($newUser);


            return Response::json(["response"=>"user-created"], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage()." | line ".$e->getLine()." | errorCode ".$e->getCode());
            return Response::noContent(500);
        }
    }

    public function signIn() {
        @["username" => $usernameOrEmail, "password" => $password] = $this->request->all();

        if (@!$usernameOrEmail || @!$password) return Response::json(["response"=>"missing-credentials"], 401);

        $usernameOrEmail = strip_tags($usernameOrEmail);
        $password = strip_tags($password);
        try {

            $user = UsersModel::where("username", $usernameOrEmail)->orWhere("email", $usernameOrEmail)->get();

            /* ->orWhere("email", $usernameOrEmail)-> */
            if (count($user) < 1) return Response::json(["response" => "not-found"], 401);

/*             return Response::json(json_encode($user[0]["dateStamp"]), 400); */
            if (time() > $user[0]["dateStamp"]) return Response::json(["response" => "datestamp-expired"], 410);

            if ($user[0]["attempts"] < 3 || $user[0]["lastAttempt"] < strtotime("-5 minute")) {
                $match = password_verify($password, $user[0]["password"]);
                if ($match) {
                    $roles = json_decode($user[0]["roles"], true);
                    $accessToken = JwtTrait::signAccessToken($user[0]["username"], $user[0]["_id"], $roles);
                    $refreshToken = JwtTrait::signRefreshToken($user[0]["username"]);
                    UsersModel::where("username", $usernameOrEmail)->orWhere("email", $usernameOrEmail)->update([
                        "validAttempt" => time(),
                        "lastAttempt" => time(),
                        "attempts" => 0,
                        "refreshToken" => $refreshToken
                    ]);

                    $cookie = cookie("refreshToken", $refreshToken, strtotime("+1 month"), "/", null, null, false, false, "Lax");

                    return Response::json(["response" => "signin-ok"], 200)->cookie($cookie)->withHeaders([
                        "Content-Type" => "application/json",
                        config('app.accessTokenHeader') => $accessToken
                    ]);

                } else {
                    $currentLastAttempt = $user[0]["lastAttempt"];
                    UsersModel::where("username", $usernameOrEmail)->orWhere("email", $usernameOrEmail)->update([
                        "lastAttempt" => time()
                    ]);

                    if (strtotime("-5 minute") >= $currentLastAttempt) {
                        UsersModel::where("username", $usernameOrEmail)->orWhere("email", $usernameOrEmail)->update([
                            "attempts" => 0
                        ]);
                                            return "1";
                    } else if ($user[0]["attempts"] < 3) {
                        UsersModel::where("username", $usernameOrEmail)->orWhere("email", $usernameOrEmail)->update([
                            "attempts" => $user[0]["attempts"]+1
                        ]);
                    }

                    return Response::json(["response" => "wrong-password"], 401);
                }
            } else {
                return Response::json(["response" => "attempts-excedeed"], 401);
            }
/*             print $match; */
        } catch (\Exception $e) {
            Log::error($e->getMessage()." | line ".$e->getLine()." | errorCode ".$e->getCode());
            return Response::noContent(500);
        }
    }

    public function logOut() {
        $refreshToken = $this->request->cookie("refreshToken");

        if (@!$refreshToken) return Response::noContent(204);

        try {
            $user = UsersModel::where("refreshToken", $refreshToken)->get();

            if (count($user) < 1) return Response::json(["response" => "log-out"], 200);

            UsersModel::where("refreshToken", $refreshToken)->update(["refreshToken" => null]);

            return Response::json(["response" => "log-out"], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage()." | line ".$e->getLine()." | errorCode ".$e->getCode());
            return Response::noContent(500);
        }
    }

    public function changePwd() {
        @["username" => $username, "oldPassword" => $oldPassword, "newPassword" => $newPassword] = $this->request->all();

        if (@!$username || @!$oldPassword || @!$newPassword) return Response::json(["response" => "missing-credentials"], 401);

        $username = strip_tags($username);
        $oldPassword = strip_tags($oldPassword);
        $newPassword = strip_tags($newPassword);
        try {
        $user = UsersModel::where("username", $username)->orWhere("email", $username)->get();

        if (count($user) < 1) return Response::json(["response" => "not-found"], 401);

        $match = password_verify($oldPassword, $user[0]["password"]);

        if ($match) {
            $oldPasswords = json_decode($user[0]["oldPasswords"]);
            $result = false;
            for ($i = 0; $i < count($oldPasswords); $i++) {
               $result = password_verify($newPassword, $oldPasswords[$i]);

               if ($result) break;
            }

            if ($result) return Response::json(["response" => "equal-to-old-password"], 401);

            if (!(preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()])[A-Za-z\d!@#$%^&*()]{8,}$/", $newPassword))) return Response::json(["response" => "invalid-password"], 400);

            $password = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 10]);
            $oldPasswords[] = $password;
            $oldPasswords = json_encode($oldPasswords);
            $dateStamp = strtotime("+1 month");
            $attempts = 0;

            UsersModel::where("username", $username)->orWhere("email", $username)->update([
                "password" => $password,
                "oldPasswords" => $oldPasswords,
                "dateStamp" => $dateStamp,
                "attempts" => $attempts
            ]);

            return Response::json(["response" => "updated-password"]);
        } else {
            return Response::json(["response" => "wrong-password"]);
        }
        } catch (\Exception $e) {
            Log::error($e->getMessage()." | line ".$e->getLine()." | errorCode ".$e->getCode());
            return Response::noContent(500);
        }
    }

}
