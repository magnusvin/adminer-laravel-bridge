<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Tests\Feature;

use AdminerBridge\AdminerBridge\Tests\TestCase;
use Illuminate\Support\Facades\Route;

/**
 * Until Adminer 6.0.1 the asset links were "../adminer/static/…", which forced
 * the asset route one level above the configured prefix - so it only resolved
 * correctly while the prefix was literally "adminer", and a nested prefix sent
 * the browser to a sibling directory that served nothing. 6.0.1 made those
 * links "./static/…" relative to the served directory, so the route now lives
 * under the prefix and any prefix works.
 *
 * The prefix is read while routes are registered during boot, so it has to be
 * set in `defineEnvironment()` rather than in a test body - hence a plain
 * PHPUnit class instead of a Pest test.
 */
class NestedPrefixRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('adminer-bridge.route.prefix', 'tools/db');
    }

    public function test_it_nests_the_asset_routes_under_a_multi_segment_prefix(): void
    {
        $this->assertSame(
            'tools/db/static/{file}',
            Route::getRoutes()->getByName('adminer-bridge.static')?->uri(),
        );

        $this->assertSame(
            'tools/db/designs/{design}/{file}',
            Route::getRoutes()->getByName('adminer-bridge.design')?->uri(),
        );
    }

    public function test_it_serves_static_assets_from_a_multi_segment_prefix(): void
    {
        $this->get('/tools/db/static/default.css')->assertOk();
        $this->get('/tools/db/static/jush/jush.css')->assertOk();
    }
}
