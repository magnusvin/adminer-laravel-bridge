<?php

declare(strict_types=1);
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;

it('has the expected default config shape', function () {
    expect(config('adminer-bridge'))->toBe([
        'route' => [
            'prefix' => 'adminer',
            'domain' => null,
            'middleware' => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
            ],
        ],
        'guard' => null,
        'session_driver' => 'file',
        'permanent_login' => true,
        'production_warning' => [
            'enabled' => null,
            'environments' => ['production'],
            'text' => 'You are viewing a production database',
            'color' => '#b91c1c',
            'position' => 'left',
        ],
        'version_check' => false,
        'jush' => true,
        'languages' => null,
        'drivers' => null,
        'themes' => null,
    ]);
});

it('lets the host app override individual config keys', function () {
    config(['adminer-bridge.route.prefix' => 'db']);

    expect(config('adminer-bridge.route.prefix'))->toBe('db');
});
