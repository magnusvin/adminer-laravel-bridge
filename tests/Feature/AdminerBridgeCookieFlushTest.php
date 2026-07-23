<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\AdminerBridge;
use Illuminate\Contracts\Cookie\QueueingFactory;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;

/**
 * Adminer's redirect()/auth_error() helpers call exit() directly, bypassing
 * Laravel's middleware entirely - so AddQueuedCookiesToResponse never gets to
 * turn a queued cookie into a real response header. AdminerBridge::serve()
 * works around this with a shutdown function that calls queuedCookieHeaders()
 * and emits the result by hand; these tests cover that method's logic without
 * needing an actual process shutdown.
 */
it('renders queued cookies as Set-Cookie headers, encrypted like a normal response would', function () {
    app(QueueingFactory::class)->queue('adminer_bridge_test', 'secret-value');

    $headers = app(AdminerBridge::class)->queuedCookieHeaders();

    expect($headers)->toHaveCount(1)
        ->and($headers[0])->toStartWith('Set-Cookie: adminer_bridge_test=')
        ->and($headers[0])->not->toContain('secret-value');
});

it('returns no headers when nothing was queued', function () {
    expect(app(AdminerBridge::class)->queuedCookieHeaders())->toBe([]);
});

it('skips cookie emission entirely when AddQueuedCookiesToResponse is not configured', function () {
    config(['adminer-bridge.route.middleware' => [EncryptCookies::class]]);

    app(QueueingFactory::class)->queue('adminer_bridge_test', 'secret-value');

    expect(app(AdminerBridge::class)->queuedCookieHeaders())->toBe([]);
});

it('leaves the cookie value in plain text when EncryptCookies is not configured', function () {
    config(['adminer-bridge.route.middleware' => [AddQueuedCookiesToResponse::class]]);

    app(QueueingFactory::class)->queue('adminer_bridge_test', 'plain-value');

    $headers = app(AdminerBridge::class)->queuedCookieHeaders();

    expect($headers)->toHaveCount(1)
        ->and($headers[0])->toContain('plain-value');
});
