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
        // If the user is already authenticated, ignore sc_user completely
        if (Auth::check()) {
            // Remove sc_user from URL if present
            if ($request->query('sc_user')) {
                return redirect($request->url());
            }

            return $next($request);
        }

        // Not authenticated — try SSO via sc_user
        $scUser = $request->query('sc_user');

        if (!$scUser) {
            return redirect()->away('about:blank');
        }

        $user = User::firstOrCreate(
            ['sc_user' => $scUser],
            [
                'name'     => str_replace('.', ' ', ucwords($scUser, '.')),
                'email'    => $scUser . '@jaguareport.local',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user, remember: true);

        // Redirect without sc_user in the URL to avoid leaking it in logs/history
        return redirect($request->url());
    }
}
