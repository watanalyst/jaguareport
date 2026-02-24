<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFromScriptcase
{
    public function handle(Request $request, Closure $next): Response
    {
        $scUser = $request->query('sc_user');

        if (!$scUser) {
            return $next($request);
        }

        // Find or create the user
        $user = User::firstOrCreate(
            ['sc_user' => $scUser],
            [
                'name'     => str_replace('.', ' ', ucwords($scUser, '.')),
                'email'    => $scUser . '@jaguareport.local',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user, remember: true);

        return $next($request);
    }
}
