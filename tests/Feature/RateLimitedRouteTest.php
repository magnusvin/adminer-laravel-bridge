<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Tests\Feature;

use AdminerBridge\AdminerBridge\Tests\TestCase;
use Illuminate\Support\Facades\Route;

/**
 * Rate limiting is read while routes are registered during boot, so it must
 * be configured before the application boots rather than inside a test's
 * body - this is a plain PHPUnit test class (instead of a Pest one) purely so
 * it can override `defineEnvironment()`, matching GuardedRouteTest.
 */
class RateLimitedRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('adminer-bridge.rate_limit', [
            'enabled' => true,
            'max_attempts' => 10,
            'decay_minutes' => 2,
        ]);
    }

    public function test_it_appends_the_configured_throttle_middleware_to_the_main_route(): void
    {
        $route = Route::getRoutes()->getByName('adminer-bridge.index');

        $this->assertContains('throttle:10,2', $route->gatherMiddleware());
    }
}
