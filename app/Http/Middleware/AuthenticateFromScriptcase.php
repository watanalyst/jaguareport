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

        // If authenticated, check if sc_user changed (different person logging in)
        if (Auth::check()) {
            if ($scUser && Auth::user()->sc_user !== $scUser) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                // Fall through to login the new user below
            } else {
                if ($scUser) {
                    return redirect($request->url());
                }
                return $next($request);
            }
        }

        // Not authenticated — try SSO via sc_user

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
