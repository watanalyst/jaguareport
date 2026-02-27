<?php

namespace App\Http\Middleware;

use App\Repositories\Logix\PermissionRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReportPermission
{
    public function handle(Request $request, Closure $next, string $appName): Response
    {
        $user = $request->user();

        if (!$user || !$user->sc_user) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $cacheKey = 'user_permissions';
        if (session()->has($cacheKey)) {
            $permissions = collect(session($cacheKey));
        } else {
            try {
                $permissions = app(PermissionRepository::class)->getAccessibleApps($user->sc_user);
            } catch (\Throwable) {
                $permissions = collect();
            }
            session([$cacheKey => $permissions->all()]);
        }

        if (!$permissions->contains($appName)) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar este relatório.');
        }

        return $next($request);
    }
}
