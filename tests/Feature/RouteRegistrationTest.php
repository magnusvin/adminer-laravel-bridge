<?php

declare(strict_types=1);

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

it('redirects a bare prefix request to the trailing-slash directory form', function () {
    $this->get('/adminer')->assertRedirect('/adminer/');
});

it('registers the main route with the configured middleware', function () {
    $route = Route::getRoutes()->getByName('adminer-bridge.index');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('GET')->toContain('POST')
        ->and($route->gatherMiddleware())->toContain(StartSession::class);
});

it('does not throttle the main route when rate limiting is disabled by default', function () {
    $route = Route::getRoutes()->getByName('adminer-bridge.index');

    expect(array_filter($route->gatherMiddleware(), fn ($m) => str_starts_with($m, 'throttle:')))->toBe([]);
});

it('registers static, jush, and design asset routes under the configured prefix', function () {
    expect(Route::getRoutes()->getByName('adminer-bridge.static'))->not->toBeNull()
        ->and(Route::getRoutes()->getByName('adminer-bridge.jush'))->not->toBeNull()
        ->and(Route::getRoutes()->getByName('adminer-bridge.design'))->not->toBeNull();
});

it('nests the jush asset route fully under the configured prefix', function () {
    $route = Route::getRoutes()->getByName('adminer-bridge.jush');

    expect($route->uri())->toBe('adminer/externals/jush/{file}');
});

it('nests the design asset route fully under the configured prefix', function () {
    $route = Route::getRoutes()->getByName('adminer-bridge.design');

    expect($route->uri())->toBe('adminer/designs/{design}/{file}');
});
