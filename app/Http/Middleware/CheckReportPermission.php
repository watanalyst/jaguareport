<?php

namespace App\Http\Middleware;

use App\Repositories\Logix\PermissionRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckReportPermission
{
    public function handle(Request $request, Closure $next, string $appName): Response
    {
        $user = $request->user();

        if (!$user || !$user->sc_user) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $cacheKey = 'user_permissions_' . $user->sc_user;

        $permissions = Cache::remember($cacheKey, 300, function () use ($user) {
            try {
                return app(PermissionRepository::class)->getAccessibleApps($user->sc_user);
            } catch (\Throwable $e) {
                Log::error('[CheckReportPermission] Oracle error: ' . $e->getMessage());
                return collect();
            }
        });

        if (!$permissions->contains($appName)) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar este relatório.');
        }

        return $next($request);
    }
}
