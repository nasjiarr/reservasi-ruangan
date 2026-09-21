<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class BreadcrumbMap
{
    /**
     * Parent route mapping for prefixes.
     *
     * @var array<string, array{label: string, index_route: string}>
     */
    protected static array $parents = [
        'rooms' => [
            'label' => 'Ruangan',
            'index_route' => 'rooms.index',
        ],
        'reservations' => [
            'label' => 'Reservasi',
            'index_route' => 'reservations.index',
        ],
        'approvals' => [
            'label' => 'Persetujuan',
            'index_route' => 'approvals.index',
        ],
        'reports' => [
            'label' => 'Laporan',
            'index_route' => 'reports.index',
        ],
        'analytics' => [
            'label' => 'Analytics',
            'index_route' => 'analytics.index',
        ],
        'profile' => [
            'label' => 'Pengaturan Profil',
            'index_route' => 'profile.edit',
        ],
    ];

    /**
     * Explicit static route breadcrumb definitions.
     *
     * @var array<string, array<int, array{label: string, href: string|null}>>
     */
    protected static array $routes = [
        'dashboard' => [
            ['label' => 'Dashboard', 'href' => null],
        ],
        'rooms.index' => [
            ['label' => 'Ruangan', 'href' => null],
        ],
        'rooms.create' => [
            ['label' => 'Ruangan', 'href' => 'rooms.index'],
            ['label' => 'Tambah Ruangan', 'href' => null],
        ],
        'rooms.edit' => [
            ['label' => 'Ruangan', 'href' => 'rooms.index'],
            ['label' => 'Edit', 'href' => null],
        ],
        'rooms.show' => [
            ['label' => 'Ruangan', 'href' => 'rooms.index'],
            ['label' => 'Detail', 'href' => null],
        ],
        'reservations.index' => [
            ['label' => 'Reservasi', 'href' => null],
        ],
        'reservations.create' => [
            ['label' => 'Reservasi', 'href' => 'reservations.index'],
            ['label' => 'Booking Ruangan', 'href' => null],
        ],
        'reservations.calendar' => [
            ['label' => 'Reservasi', 'href' => 'reservations.index'],
            ['label' => 'Kalender Jadwal', 'href' => null],
        ],
        'reservations.show' => [
            ['label' => 'Reservasi', 'href' => 'reservations.index'],
            ['label' => 'Detail', 'href' => null],
        ],
        'approvals.index' => [
            ['label' => 'Persetujuan', 'href' => null],
        ],
        'reports.index' => [
            ['label' => 'Laporan', 'href' => null],
        ],
        'analytics.index' => [
            ['label' => 'Analytics', 'href' => null],
        ],
        'profile.edit' => [
            ['label' => 'Pengaturan Profil', 'href' => null],
        ],
    ];

    /**
     * Generate hierarchical breadcrumb list for the given route name.
     *
     * @param string|null $routeName
     * @return array<int, array{label: string, href: string|null}>
     */
    public static function generateBreadcrumbs(?string $routeName): array
    {
        if (!$routeName) {
            return [
                ['label' => 'Dashboard', 'href' => null],
            ];
        }

        // 1. Check explicit route configuration
        if (isset(self::$routes[$routeName])) {
            return array_map(function ($item) {
                return [
                    'label' => $item['label'],
                    'href' => $item['href'] && Route::has($item['href'])
                        ? route($item['href'])
                        : null,
                ];
            }, self::$routes[$routeName]);
        }

        // 2. Generic fallback parser for dot-notation routes (e.g. 'users.create')
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            $prefix = $parts[0];
            $action = $parts[1];

            $parentLabel = self::$parents[$prefix]['label'] ?? ucfirst($prefix);
            $indexRoute = self::$parents[$prefix]['index_route'] ?? ($prefix . '.index');
            $parentHref = Route::has($indexRoute) ? route($indexRoute) : null;

            $actionLabels = [
                'index' => 'Daftar',
                'create' => 'Tambah',
                'edit' => 'Edit',
                'show' => 'Detail',
            ];
            $actionLabel = $actionLabels[$action] ?? ucfirst($action);

            return [
                ['label' => $parentLabel, 'href' => $parentHref],
                ['label' => $actionLabel, 'href' => null],
            ];
        }

        // 3. Fallback for single-level route
        return [
            ['label' => ucfirst($routeName), 'href' => null],
        ];
    }
}

