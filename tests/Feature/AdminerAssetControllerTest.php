<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\Http\Controllers\AdminerAssetController;
use Composer\InstalledVersions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('serves a real static asset through the configured route', function () {
    $response = $this->get('/adminer/static/functions.js');

    $response->assertOk();
    expect($response->getFile()->getRealPath())
        ->toBe(realpath(InstalledVersions::getInstallPath('vrana/adminer').'/adminer/static/functions.js'));
});

it('404s for a static asset that does not exist', function () {
    $this->get('/adminer/static/does-not-exist.js')->assertNotFound();
});

it('blocks path traversal attempts on the static controller', function () {
    (new AdminerAssetController)->static('../../../../../../../../../../etc/passwd');
})->throws(NotFoundHttpException::class);

it('serves jush from the vrana/jush package, where Adminer ships an empty submodule directory', function () {
    $response = $this->get('/adminer/static/jush/jush.css');

    $response->assertOk();
    expect($response->getFile()->getRealPath())
        ->toBe(realpath(InstalledVersions::getInstallPath('vrana/jush').'/jush.css'));
});

it('blocks path traversal attempts through the jush prefix', function () {
    (new AdminerAssetController)->static('jush/../../../../../../../../../../etc/passwd');
})->throws(NotFoundHttpException::class);

it('404s for a jush asset that does not exist', function () {
    $this->get('/adminer/static/jush/does-not-exist.css')->assertNotFound();
});

/**
 * Adminer moves its static files around between releases - 6.0.1 relocated
 * jush into this directory, 6.0.2 added worker.js, 6.1.0 renamed logo.png to
 * logo.svg. Enumerating what the pinned release actually ships keeps this
 * honest across those bumps: whatever is in there has to be reachable.
 */
it('serves every static file the pinned Adminer release ships', function () {
    $directory = InstalledVersions::getInstallPath('vrana/adminer').'/adminer/static';
    $files = array_values(array_filter(
        scandir($directory) ?: [],
        fn (string $entry): bool => is_file($directory.'/'.$entry),
    ));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $this->get("/adminer/static/{$file}")->assertOk();
    }
});

it('serves static assets with the content type the browser needs to honour them', function (string $file, string $contentType) {
    $this->get("/adminer/static/{$file}")
        ->assertOk()
        ->assertHeader('Content-Type', $contentType);
})->with([
    ['default.css', 'text/css; charset=utf-8'],
    ['functions.js', 'text/javascript; charset=utf-8'],
    ['jush/jush.css', 'text/css; charset=utf-8'],
    ['jush/modules/jush.js', 'text/javascript; charset=utf-8'],
]);

/**
 * The extension is upstream's to change (it went from .png to .svg in 6.1.0),
 * so this asserts the part that is the bridge's problem: whatever the logo is
 * called, it reaches the browser as an image.
 */
it('serves the Adminer logo as an image, whatever extension upstream ships', function () {
    $logos = glob(InstalledVersions::getInstallPath('vrana/adminer').'/adminer/static/logo.*') ?: [];

    expect($logos)->toHaveCount(1);

    $response = $this->get('/adminer/static/'.basename($logos[0]));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('image/');
});

it('serves a real design asset through the configured route', function () {
    $response = $this->get('/adminer/designs/nette/adminer.css');

    $response->assertOk();
    expect($response->getFile()->getRealPath())
        ->toBe(realpath(InstalledVersions::getInstallPath('vrana/adminer').'/designs/nette/adminer.css'));
});

it('404s for a design asset that does not exist', function () {
    $this->get('/adminer/designs/nette/does-not-exist.css')->assertNotFound();
});

it('blocks path traversal attempts on the design name', function () {
    (new AdminerAssetController)->design('../../../../../../../../../../etc', 'passwd');
})->throws(NotFoundHttpException::class);

it('blocks path traversal attempts on the design file', function () {
    (new AdminerAssetController)->design('nette', '../../../../../../../../../../etc/passwd');
})->throws(NotFoundHttpException::class);
