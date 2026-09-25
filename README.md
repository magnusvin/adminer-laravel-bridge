<div align="center">
    <h1>Adminer Bridge</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/magnusvin/adminer-laravel-bridge"><img src="https://img.shields.io/packagist/v/magnusvin/adminer-laravel-bridge.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/magnusvin/adminer-laravel-bridge"><img src="https://img.shields.io/packagist/php-v/magnusvin/adminer-laravel-bridge.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/magnusvin/adminer-laravel-bridge"><img src="https://badge.laravel.cloud/badge/magnusvin/adminer-laravel-bridge?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/magnusvin/adminer-laravel-bridge/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/magnusvin/adminer-laravel-bridge/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/magnusvin/adminer-laravel-bridge"><img src="https://img.shields.io/packagist/dt/magnusvin/adminer-laravel-bridge.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A Laravel bridge for [Adminer](https://www.adminer.org/), the single-file database management tool by Jakub Vrána. This package wires Adminer into a Laravel app as a first-class route: sessions, cookies, and CSRF/auth all flow through Laravel's own stack instead of Adminer running as a bare standalone script, while still letting Adminer's own code run untouched.

## Requirements

- PHP 8.3+
- Laravel 12.x or 13.x
- [`vrana/adminer`](https://packagist.org/packages/vrana/adminer) 6.1.0 (installed automatically as a dependency, pinned to an exact version — see [Versioning](#versioning))

## Installation

You can install the package via Composer:

```bash
composer require magnusvin/adminer-laravel-bridge
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="adminer-bridge-config"
```

(`--tag="adminer-bridge"` publishes the same file — both tags are registered for convenience.) There are no views or public assets to publish: Adminer renders its own markup, and its static assets, `jush` syntax-highlighting files, and design themes are served dynamically through routes rather than copied into your app.

This is the published `config/adminer-bridge.php`:

```php
return [

    'route' => [
        'prefix' => 'adminer',
        'domain' => null,
        'middleware' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ],
    ],

    // e.g. 'web'; gates access to the Adminer route via Laravel's Authenticate middleware.
    'guard' => null,

    // Throttles the Adminer route using Laravel's own rate limiter. Disabled by default.
    'rate_limit' => [
        'enabled' => false,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    // null defers to the app's own config('session.driver'); or force a specific
    // Laravel session driver (file, database, redis, ...) just for Adminer.
    'session_driver' => 'file',

    // Disables Adminer's cookie-based "remember me" persistence when false.
    'permanent_login' => true,

    // Shows a fixed, always-visible banner on every Adminer page - handy for
    // flagging a production database. Auto-detects from 'environments' below.
    'production_warning' => [
        'enabled' => null,
        'environments' => ['production'],
        'text' => 'You are viewing a production database',
        'color' => '#b91c1c',
        'position' => 'left', // 'top', 'bottom', or 'left'
    ],

    // Disables Adminer's outbound "new version available" check when false.
    'version_check' => false,

    // Disables jush (SQL/JS syntax highlighting) when false.
    'jush' => true,

    // null allows every bundled language. Restrict (and enforce server-side)
    // the language switcher to a specific set of codes, e.g. ['en'].
    'languages' => null,

    // null allows every bundled driver. Restrict (and enforce server-side)
    // the login driver dropdown, e.g. ['server', 'sqlite'].
    'drivers' => null,

    // null offers only the built-in light/dark styling. Restrict the design
    // switcher to a set of bundled theme names (folders under vrana/adminer's
    // designs/ directory, e.g. ['nette', 'dracula']).
    'themes' => null,
];
```

By default the Adminer UI is available at `/adminer`. Since Adminer handles its own login and there is no built-in gate, set `guard` (and/or wrap the route behind your own middleware via `route.middleware`) before exposing this in any environment reachable outside your team.

## Versioning

This package tracks [Adminer](https://www.adminer.org/)'s own release cycle instead of an independent semver line. A version has four segments:

```
6 . 0 . 1 . 2
└───┬───┘   └── this package's own patch counter
    └────────── the exact vrana/adminer release the package ships
```

- A bridge-only fix (no Adminer version change) bumps the last segment, e.g. `6.0.1.0` → `6.0.1.1`.
- A new Adminer release resets the last segment to `0` and adopts Adminer's version, e.g. `6.0.1.2` → `6.0.2.0`.

`vrana/adminer` is required at an **exact version**, so the mapping is a guarantee and not a convention: installing `6.0.1.*` of this package always gives you Adminer 6.0.1, never a minor or patch that arrived upstream after this bridge release was tested. Every Adminer release gets its own bridge release, so an upstream upgrade is always something you opt into.

### Choosing a constraint

The trade-off is how much of Adminer you let move on a `composer update`. The four segments make that a dial rather than a switch:

| Constraint | Adminer moves | Bridge fixes | Use when |
| --- | --- | --- | --- |
| `6.0.1.0` | never | never | you want a byte-identical install, pinned by the lock file anyway |
| `~6.0.1.0` | never | yes | **recommended** - bridge fixes only, Adminer frozen at 6.0.1 |
| `~6.0.1` | 6.0.x patches | yes | you want upstream security patches, no feature changes |
| `^6.0` | all 6.x | yes | you follow Adminer's 6.x line and read its changelog |

`6.0.1.*`, `6.0.*` and `6.*` are equivalent to rows two, three and four respectively, if you prefer wildcards.

Note that this is **not** ordinary semver: the first segment changes whenever upstream Adminer's does, not when this package breaks its own API. So `^6.0` does not mean "no breaking changes" - it means "any Adminer 6.x", and Adminer's minor releases do move things (6.1.0 renamed `logo.png` to `logo.svg`, for instance). Pick a row, then read the [CHANGELOG](CHANGELOG.md) before widening it - each entry summarises what changed upstream, links Adminer's own changelog, and calls out anything that changed in the bridge itself.

## Release automation

Releases are cut automatically. A scheduled workflow checks Packagist daily for a new stable `vrana/adminer`, and when it finds one it moves the pin, pushes the bump to an `adminer/<version>` branch, and waits for the full test matrix and the asset smoke test to finish on that branch. Only if everything is green does it fast-forward `main`, tag `<adminer version>.0`, and publish a release quoting Adminer's own changelog for that version. If anything is red it releases nothing and opens an issue, leaving the branch in place to pick up by hand.

The same workflow also chooses `vrana/jush`. Adminer carries jush as a Git submodule pinned to an exact commit, frequently ahead of the newest tagged jush release, and Composer can only depend on releases — so the constraint is set to the newest jush release that is not newer than the commit the pinned Adminer bundles. Getting that wrong is quiet: jush is served as static files, so a mismatched version still answers every request with a 200 and only misbehaves once the browser runs it.

Automating this is only reasonable because of the exact pin: a new bridge release cannot reach anyone who did not choose a constraint wide enough to accept it, so tracking upstream quickly costs you nothing you did not opt into.

What the gate does and does not prove is worth knowing if you run a wide constraint. It covers this package's wiring — service provider, routes, middleware, config, publishing, session and cookie handling — and it covers asset serving from both ends: the test suite requires every static file the pinned Adminer ships to be served with a sane content type, and the smoke test renders a real page over HTTP and requires every asset Adminer links to to resolve. That second half exists because Adminer moved its asset paths in 6.0.1 and renamed its logo in 6.1.0, and a route test alone cannot see either. A reflection test also fails the build if an overridden method stops existing upstream, so a hook cannot silently die.

It does not connect to a database. Nothing behind Adminer's login — select, edit, export, SQL command — is exercised, and behaviour changes inside Adminer's own pages will pass the gate. Read Adminer's changelog, linked from every release, before widening your constraint across a version boundary.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Adminer Bridge! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [magnusvin](https://github.com/magnusvin)
- [Jakub Vrána](https://github.com/vrana) and [Adminer](https://github.com/vrana/adminer)'s contributors — this package is a Laravel bridge around their database tool and its bundled `jush` syntax highlighter, not a reimplementation of it
- [All Contributors](../../contributors)

## License

Adminer Bridge is open-sourced software licensed under the [MIT license](LICENSE.md). Adminer itself (`vrana/adminer`) and `vrana/jush` are separate upstream projects with their own licensing — see their respective repositories for details.
