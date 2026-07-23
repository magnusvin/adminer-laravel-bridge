<?php

declare(strict_types=1);
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;

return [

    'route' => [
        'prefix' => 'adminer',
        'domain' => null,
        'middleware' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ],
    ],

    // e.g. 'web'; gates access to the Adminer route via Laravel's Authenticate middleware.
    // null disables the gate (only route.middleware protects the route).
    'guard' => null,

    // Throttles the Adminer route using Laravel's own rate limiter (the same
    // mechanism as the framework's `throttle` middleware) - keyed by the
    // authenticated user if any, otherwise by IP address. Disabled by default.
    'rate_limit' => [
        'enabled' => false,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    // null defers to the app's own config('session.driver'); or force a specific
    // Laravel session driver (file, database, redis, ...) just for Adminer.
    'session_driver' => 'file',

    // Disables Adminer's cookie-based "remember me" persistence when false.
    // The checkbox itself still renders (Adminer draws it via a raw echo that
    // isn't routed through an overridable hook) - this only disables the effect.
    'permanent_login' => true,

    // Shows a fixed, always-visible banner with the given text on every Adminer
    // page. null/'' text renders nothing even if enabled.
    //   - 'enabled' => null (default): auto-detect from 'environments' below via
    //     app()->environment(...).
    //   - 'enabled' => true/false: explicit override, ignoring the app environment.
    'production_warning' => [
        'enabled' => null,
        'environments' => ['production'],
        'text' => 'You are viewing a production database',
        'color' => '#b91c1c',
        'position' => 'left', // 'top', 'bottom', or 'left' (a small corner badge, doesn't overlay content)
    ],

    // Disables Adminer's "new version available" check (an outbound request to
    // adminer.org triggered client-side) when false.
    'version_check' => false,

    // Disables jush (SQL/JS syntax highlighting assets and script) when false.
    'jush' => true,

    // null allows every bundled language. Restrict the language switcher (and
    // enforce the restriction server-side) to a specific set of codes, e.g.
    // ['en']. An empty array or single-entry array hides the switcher entirely.
    'languages' => null,

    // null allows every bundled driver. Restrict the login driver dropdown (and
    // enforce the restriction server-side) to a specific set of driver ids, e.g.
    // ['server', 'sqlite'] ('server' is Adminer's own id for MySQL/MariaDB). A
    // single-entry array hides the dropdown and preselects that driver.
    'drivers' => null,

    // null offers only the built-in light/dark styling. Restrict the design
    // switcher to a set of bundled theme names (folder names under
    // vrana/adminer's designs/ directory, e.g. ['nette', 'dracula']).
    'themes' => null,
];
