<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\Http\Controllers\AdminerAssetController;
use AdminerBridge\AdminerBridge\Http\Controllers\AdminerController;
use Illuminate\Support\Facades\Route;

$prefix = trim((string) config('adminer-bridge.route.prefix'), '/');

$middleware = (array) config('adminer-bridge.route.middleware', []);

$guard = config('adminer-bridge.guard');

if ($guard) {
    $middleware[] = 'auth:'.$guard;
}

$rateLimit = (array) config('adminer-bridge.rate_limit', []);

if ($rateLimit['enabled'] ?? false) {
    $middleware[] = 'throttle:'.($rateLimit['max_attempts'] ?? 60).','.($rateLimit['decay_minutes'] ?? 1);
}

/**
 * Adminer 6.0.1 made its development version runnable from the adminer/
 * directory under any name, which turned every hardcoded asset link into a
 * "./static/…" path relative to the served directory - so the asset route can
 * now simply live under the configured prefix. Before that, the links were
 * "../adminer/static/…" and this route had to be anchored one level above the
 * prefix, which only worked cleanly while the prefix was literally "adminer".
 *
 * jush moved into adminer/static/jush in the same release, so it arrives
 * through this same route rather than a dedicated one - see
 * AdminerAssetController::static() for why it is not served from there.
 */
Route::domain(config('adminer-bridge.route.domain'))->group(function () use ($prefix, $middleware) {
    Route::get("{$prefix}/static/{file}", [AdminerAssetController::class, 'static'])
        ->where('file', '.*')
        ->name('adminer-bridge.static');

    Route::get("{$prefix}/designs/{design}/{file}", [AdminerAssetController::class, 'design'])
        ->where('design', '[\w-]+')
        ->where('file', '.*')
        ->name('adminer-bridge.design');

    Route::match(['get', 'post'], "{$prefix}/{any?}", [AdminerController::class, '__invoke'])
        ->where('any', '.*')
        ->middleware($middleware)
        ->name('adminer-bridge.index');
});
