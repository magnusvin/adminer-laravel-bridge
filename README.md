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
- [`vrana/adminer`](https://packagist.org/packages/vrana/adminer) ^5.5 (installed automatically as a dependency)

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

This package tracks [Adminer](https://www.adminer.org/)'s own release cycle instead of an independent semver line. A version has four segments: the first three mirror the `vrana/adminer` release the package was built against, and the fourth is this package's own patch counter for fixes made without an upstream Adminer bump.

- A bridge-only fix (no Adminer version change) bumps the last segment, e.g. `5.5.1.0` → `5.5.1.1`.
- A new Adminer release resets the last segment to `0` and adopts Adminer's new version, e.g. `5.5.1.4` → `5.6.0.0`.

Composer accepts this four-segment format natively, but keep in mind it does **not** carry the usual semver guarantee that the first number only changes on a breaking change *to this package's own API* — it changes whenever upstream Adminer does. Pin a full version (or a `~5.5.1` style constraint) rather than a broad `^5` if you want to control upgrades explicitly, and check the [CHANGELOG](CHANGELOG.md) before bumping across an Adminer version boundary.

## Roadmap

- Dependabot is already configured for GitHub Actions and Composer dependencies (weekly); once tagged releases exist, it can also be pointed at `vrana/adminer` version bumps to prompt a matching bridge release.
- Future releases are intended to follow shortly after each upstream Adminer tag, keeping the first three version segments in sync with `vrana/adminer`.
- Contributions and issue reports that help track Adminer's release cadence are welcome — see [Contributing](#contributing) below.

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
