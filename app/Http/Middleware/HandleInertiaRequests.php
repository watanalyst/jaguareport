<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
            'navigation' => $this->buildNavigation(),
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function buildNavigation(): array
    {
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
}
