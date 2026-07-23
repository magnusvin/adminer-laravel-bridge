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

it('blocks path traversal attempts on the jush controller', function () {
    (new AdminerAssetController)->jush('../../../../../../../../../../etc/passwd');
})->throws(NotFoundHttpException::class);

it('404s for a jush asset that does not exist', function () {
    $this->get('/adminer/externals/jush/does-not-exist.css')->assertNotFound();
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
