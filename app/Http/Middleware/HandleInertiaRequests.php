<?php

namespace App\Http\Middleware;

use App\Repositories\Logix\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn () => [
                'user' => $request->user(),
            ],
            'navigation' => fn () => $this->buildNavigation($request),
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function buildNavigation(Request $request): array
    {
        $allowedApps = $this->getUserPermissions($request);

        $nav = [];
        $reports = config('reports', []);

        foreach ($reports as $moduleKey => $module) {
            if (!is_array($module) || !isset($module['label'])) {
                continue;
            }

            $section = [
                'key'   => $moduleKey,
                'title' => $module['label'],
                'items' => [],
            ];

            foreach ($module as $key => $report) {
                if ($key === 'label' || !is_array($report) || !isset($report['route'])) {
                    continue;
                }

                $appName = $report['app_name'] ?? null;
                if (!$appName || !$allowedApps->contains($appName)) {
                    continue;
                }

                $section['items'][] = [
                    'label'     => $report['label'],
                    'routeName' => $report['route'],
                ];
            }

            if (!empty($section['items'])) {
                $nav[] = $section;
            }
        }

        return $nav;
    }

    private function getUserPermissions(Request $request): \Illuminate\Support\Collection
    {
        $user = $request->user();
        if (!$user || !$user->sc_user) {
            Log::debug('[Permissions] No user or sc_user');
            return collect();
        }

        $cacheKey = 'user_permissions';
        if (session()->has($cacheKey)) {
            $cached = collect(session($cacheKey));
            Log::debug('[Permissions] From cache for ' . $user->sc_user, ['permissions' => $cached->all()]);
            return $cached;
        }

        try {
            $permissions = app(PermissionRepository::class)->getAccessibleApps($user->sc_user);
        } catch (\Throwable $e) {
            Log::error('[Permissions] Oracle error: ' . $e->getMessage());
            $permissions = collect();
        }

        Log::debug('[Permissions] From Oracle for ' . $user->sc_user, ['permissions' => $permissions->all()]);
        session([$cacheKey => $permissions->all()]);

        return $permissions;
    }
}
