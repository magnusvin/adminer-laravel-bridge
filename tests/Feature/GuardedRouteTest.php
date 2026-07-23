<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Tests\Feature;

use AdminerBridge\AdminerBridge\Tests\TestCase;
use Illuminate\Support\Facades\Route;

/**
 * The guard is read while routes are registered during boot, so it must be
 * configured before the application boots rather than inside a test's body -
 * this is a plain PHPUnit test class (instead of a Pest one) purely so it can
 * override `defineEnvironment()`.
 */
class GuardedRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('adminer-bridge.guard', 'web');
    }

    public function test_it_appends_the_guard_middleware_to_the_main_route(): void
    {
        $route = Route::getRoutes()->getByName('adminer-bridge.index');

        $this->assertContains('auth:web', $route->gatherMiddleware());
    }
}
