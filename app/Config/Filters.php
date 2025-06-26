<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'auth'          => \App\Filters\Auth::class,
        'rbac'          => \App\Filters\Rbac::class,
        'guest'         => \App\Filters\GuestFilter::class, // Add this alias
    ];

    /**
     * List of special required filters.
     *
     * The filters listed here are special. They are applied before and after
     * other kinds of filters, and always applied even if a route does not exist.
     *
     * Filters set by default provide framework functionality. If removed,
     * those functions will no longer work.
     *
     * @see https://codeigniter.com/user_guide/incoming/filters.html#provided-filters
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
             

        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar
        ],
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            'csrf',
            // 'invalidchars',
            'auth' => [
                'except' => [
                    'login',
                    'login/auth',
                    'login/logout',
                ]
            ],
            'rbac' => [
                'except' => [
                    'login',
                    'login/auth',
                    'login/logout',
                    'dashboard',
                    'dashboard/*',
                    'password', // Exclude the entire password group
                    'password/*', // Exclude all sub-routes of password
                    'setting/users/save', // Exclude user save route
                    'setting/users/delete/*', // Exclude user delete route
                    'setting/getrolemenus/(:any)',
                    'setting/users/getuserroles/(:any)', // Kecualikan AJAX endpoint jika halaman utama sudah dilindungi RBAC
                    // 'setting/roles', // Corrected: no leading slash
                    'setting/roles/*', // Corrected: no leading slash, covers sub-routes
                    'setting/roles/save', // Corrected: full path, no leading slash
                    'setting/roles/delete/*', // Corrected: full path, no leading slash
                    'setting/menu/updateorder', // Exclude AJAX endpoint, as it has its own RBAC check
                ]
            ],
        ],
        'after'  => [
            // 'auth' filter removed from 'after' group
            // 'honeypot',
            // 'secureheaders',
        ],
    ];
    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
