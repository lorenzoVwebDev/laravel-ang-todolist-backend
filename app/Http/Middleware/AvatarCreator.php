<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class AvatarCreator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    private $tempAvatarUuid = "";

    public function handle(Request $request, Closure $next): Response
    {


        if ($request->hasFile('avatar')) {
          if (!($request->avatar->extension() === "png" || $request->avatar->extension() === "jpg")) return ResponseFacade::json(["response" => "invalid-avatar-extension"], 400);
          $tempAvatar = $request->file('avatar');
          $tempAvatarUuid = Str::uuid();
          Storage::putFileAs('avatars', $tempAvatar, $tempAvatarUuid);
          $this->tempAvatarUuid = $tempAvatarUuid;
          $request->merge([
            "tempAvatarUuid" => $this->tempAvatarUuid
          ]);
        }
/*         $file = Storage::get("avatars/".$tempAvatarUuid);
        Storage::delete('avatars/'.$tempAvatarUuid);
        return ResponseFacade::json(["response" => base64_encode($file)], 400); */
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void {
        if ($this->tempAvatarUuid) Storage::delete("avatars/".$this->tempAvatarUuid);
    }
}
