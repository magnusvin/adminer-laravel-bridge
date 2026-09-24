<?php

declare(strict_types=1);

it('leaves the jush stylesheet links pointing at the static asset route', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    ob_start();
    adminer_object()->head(null);
    $html = ob_get_clean();

    expect($html)->toContain('./static/jush/jush.css')
        ->toContain('./static/jush/jush-dark.css')
        ->not->toContain('../externals/jush/');
});

it('auto-detects the production warning from the app environment when enabled is null', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    config(['adminer-bridge.production_warning' => [
        'enabled' => null,
        'environments' => ['testing'],
        'text' => 'Careful!',
    ]]);

    ob_start();
    adminer_object()->head(null);
    $html = ob_get_clean();

    expect($html)->toContain("banner.textContent = 'Careful!';");
});

it('does not show the production warning when the environment does not match', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    config(['adminer-bridge.production_warning' => [
        'enabled' => null,
        'environments' => ['production'],
        'text' => 'Careful!',
    ]]);

    ob_start();
    adminer_object()->head(null);
    $html = ob_get_clean();

    expect($html)->not->toContain('banner');
});

it('wires the version_check config through to verifyVersion()', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    config(['adminer-bridge.version_check' => false]);

    expect(adminer_object()->verifyVersion())->toBeFalse();
});

it('lets an explicit enabled flag override the app environment', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    config(['adminer-bridge.production_warning' => [
        'enabled' => true,
        'environments' => ['production'],
        'text' => 'Careful!',
    ]]);

    ob_start();
    adminer_object()->head(null);
    $html = ob_get_clean();

    expect($html)->toContain("banner.textContent = 'Careful!';");
});
