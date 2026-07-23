<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\AdminerAccessGuard;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->originalPost = $_POST;
    $this->originalCookie = $_COOKIE;
    $this->originalSession = $_SESSION ?? [];
    $this->originalAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
});

afterEach(function () {
    $_POST = $this->originalPost;
    $_COOKIE = $this->originalCookie;
    $_SESSION = $this->originalSession;

    if ($this->originalAcceptLanguage === null) {
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    } else {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->originalAcceptLanguage;
    }
});

it('allows any driver when no allow-list is configured', function () {
    config(['adminer-bridge.drivers' => null]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/?pgsql='));

    $guard->enforceAllowedDriver();
})->throwsNoExceptions();

it('blocks a query-string driver outside the allow-list', function () {
    config(['adminer-bridge.drivers' => ['sqlite']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/?pgsql='));

    expect(fn () => $guard->enforceAllowedDriver())
        ->toThrow(HttpException::class);
});

it('allows a query-string driver inside the allow-list', function () {
    config(['adminer-bridge.drivers' => ['sqlite']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/?sqlite='));

    $guard->enforceAllowedDriver();
})->throwsNoExceptions();

it('never blocks the bare login page, even when mysql is not allowed', function () {
    config(['adminer-bridge.drivers' => ['sqlite']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/'));

    $guard->enforceAllowedDriver();
})->throwsNoExceptions();

it('validates the mysql "server" query key like any other driver key', function () {
    config(['adminer-bridge.drivers' => ['sqlite']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/?server=localhost'));

    expect(fn () => $guard->enforceAllowedDriver())
        ->toThrow(HttpException::class);
});

it('allows the mysql "server" query key when mysql is allowed', function () {
    config(['adminer-bridge.drivers' => ['server']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/?server=localhost'));

    $guard->enforceAllowedDriver();
})->throwsNoExceptions();

it('blocks a disallowed driver posted from the login form', function () {
    config(['adminer-bridge.drivers' => ['sqlite']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/', 'POST', [
        'auth' => ['driver' => 'pgsql'],
    ]));

    expect(fn () => $guard->enforceAllowedDriver())
        ->toThrow(HttpException::class);
});

it('allows an allowed driver posted from the login form', function () {
    config(['adminer-bridge.drivers' => ['pgsql']]);

    $guard = new AdminerAccessGuard(Request::create('/adminer/', 'POST', [
        'auth' => ['driver' => 'pgsql'],
    ]));

    $guard->enforceAllowedDriver();
})->throwsNoExceptions();

it('leaves the language request untouched when no allow-list is configured', function () {
    config(['adminer-bridge.languages' => null]);
    $_POST['lang'] = 'de';

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_POST['lang'])->toBe('de');
});

it('strips a disallowed posted language', function () {
    config(['adminer-bridge.languages' => ['en']]);
    $_POST['lang'] = 'de';

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_POST)->not->toHaveKey('lang');
});

it('keeps an allowed posted language', function () {
    config(['adminer-bridge.languages' => ['en', 'de']]);
    $_POST['lang'] = 'de';

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_POST['lang'])->toBe('de');
});

it('strips a disallowed language cookie', function () {
    config(['adminer-bridge.languages' => ['en']]);
    $_COOKIE['adminer_lang'] = 'fr';

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_COOKIE)->not->toHaveKey('adminer_lang');
});

it('strips a disallowed session language', function () {
    config(['adminer-bridge.languages' => ['en']]);
    $_SESSION['lang'] = 'fr';

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_SESSION)->not->toHaveKey('lang');
});

it('narrows the Accept-Language fallback to the allow-list', function () {
    config(['adminer-bridge.languages' => ['en', 'de']]);

    (new AdminerAccessGuard(Request::create('/adminer/')))->sanitizeLanguageRequest();

    expect($_SERVER['HTTP_ACCEPT_LANGUAGE'])->toBe('en,de');
});
