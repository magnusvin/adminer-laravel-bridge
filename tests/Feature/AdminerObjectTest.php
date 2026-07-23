<?php

declare(strict_types=1);

it('rewrites the jush stylesheet links to the nested route', function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../src/adminer-object.php';

    ob_start();
    adminer_object()->head(null);
    $html = ob_get_clean();

    expect($html)->toContain(route('adminer-bridge.jush', ['file' => 'jush.css']))
        ->toContain(route('adminer-bridge.jush', ['file' => 'jush-dark.css']))
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
