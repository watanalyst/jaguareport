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
            // No sc_user param — allow if already authenticated
            if (Auth::check()) {
                return $next($request);
            }

            return redirect()->away('about:blank');
        }

        $currentUser = Auth::user();

        // If already logged in as the same sc_user, skip re-auth
        if ($currentUser && $currentUser->sc_user === $scUser) {
            return $next($request);
        }

        // Different user or not logged in — switch session
        if ($currentUser) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
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

        return $next($request);
    }
}
