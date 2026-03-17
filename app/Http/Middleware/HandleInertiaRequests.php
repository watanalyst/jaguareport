<?php

namespace App\Http\Middleware;

use App\Repositories\Logix\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

            foreach ($module as $key => $entry) {
                if ($key === 'label' || !is_array($entry)) {
                    continue;
                }

                // Subgroup (has 'children' key)
                if (isset($entry['children'])) {
                    $children = [];
                    foreach ($entry['children'] as $childKey => $report) {
                        if (!is_array($report) || !isset($report['route'])) {
                            continue;
                        }
                        $appName = $report['app_name'] ?? null;
                        if (!$appName || !$allowedApps->contains($appName)) {
                            continue;
                        }
                        $children[] = [
                            'label'     => $report['label'],
                            'routeName' => $report['route'],
                            'href'      => route($report['route'], [], false),
                        ];
                    }
                    if (!empty($children)) {
                        $section['items'][] = [
                            'label'    => $entry['label'],
                            'key'      => $key,
                            'children' => $children,
                        ];
                    }
                    continue;
                }

                // Direct item (no subgroup)
                if (!isset($entry['route'])) {
                    continue;
                }
                $appName = $entry['app_name'] ?? null;
                if (!$appName || !$allowedApps->contains($appName)) {
                    continue;
                }
                $section['items'][] = [
                    'label'     => $entry['label'],
                    'routeName' => $entry['route'],
                    'href'      => route($entry['route'], [], false),
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

        $cacheKey = 'user_permissions_' . $user->sc_user;

        return Cache::remember($cacheKey, 300, function () use ($user) {
            try {
                $permissions = app(PermissionRepository::class)->getAccessibleApps($user->sc_user);
            } catch (\Throwable $e) {
                Log::error('[Permissions] Oracle error: ' . $e->getMessage());
                $permissions = collect();
            }

            Log::debug('[Permissions] From Oracle for ' . $user->sc_user, ['permissions' => $permissions->all()]);

            return $permissions;
        });
    }
}
