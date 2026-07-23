<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\Http\Controllers\AdminerAssetController;
use AdminerBridge\AdminerBridge\Http\Controllers\AdminerController;
use Illuminate\Support\Facades\Route;

$prefix = trim((string) config('adminer-bridge.route.prefix'), '/');
$parent = trim(dirname($prefix), '/.');
$assetPrefix = $parent === '' ? '' : "{$parent}/";

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
 * Adminer's own markup hardcodes "../adminer/static/…" relative to whatever
 * directory it's served from, so that route must be anchored one level above
 * the configured prefix — never off the prefix itself, which only happened
 * to work while the prefix was literally "adminer".
 *
 * The jush asset links are rewritten to this named route by adminer-object.php
 * (see head() and syntaxHighlighting() there), so this route is free to live
 * fully nested under the configured prefix instead of following Adminer's
 * hardcoded "../externals/jush/…" convention.
 */
Route::domain(config('adminer-bridge.route.domain'))->group(function () use ($prefix, $assetPrefix, $middleware) {
    Route::get("{$assetPrefix}adminer/static/{file}", [AdminerAssetController::class, 'static'])
        ->where('file', '.*')
        ->name('adminer-bridge.static');

    Route::get("{$prefix}/externals/jush/{file}", [AdminerAssetController::class, 'jush'])
        ->where('file', '.*')
        ->name('adminer-bridge.jush');

    Route::get("{$prefix}/designs/{design}/{file}", [AdminerAssetController::class, 'design'])
        ->where('design', '[\w-]+')
        ->where('file', '.*')
        ->name('adminer-bridge.design');

    Route::match(['get', 'post'], "{$prefix}/{any?}", [AdminerController::class, '__invoke'])
        ->where('any', '.*')
        ->middleware($middleware)
        ->name('adminer-bridge.index');
});
