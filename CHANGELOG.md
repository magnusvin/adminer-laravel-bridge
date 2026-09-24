# Release Notes

## [Unreleased](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.1.0...HEAD)

No unreleased changes yet. See [Versioning](README.md#versioning) for how release version numbers are chosen.

## [6.0.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.1...6.0.1.0) - 2026-09-24

Ships **Adminer 6.0.1** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.0.1/CHANGELOG.md)).

### Adminer 6.0.1

Security and session handling: the CSRF token is now verified when logging in and before killing a client-side timed-out query, the session cookie is sent with `SameSite=lax`, and an invalid CSRF token or oversized POST returns an error status instead of a normal page. Elasticsearch and MS SQL got substantial driver work, OpenSearch is supported, the database schema page arranges tables by their foreign keys, and Select can modify or delete several rows in a transaction.

### Changed in the bridge

Adminer 6.0.1 made its development version runnable from the `adminer/` directory under any name, which changed every hardcoded asset link from `../adminer/static/…` to `./static/…`, and moved jush out of `externals/` into `adminer/static/jush`. Both needed handling here:

- **The static asset route moved under the configured prefix.** It used to be anchored one level above it to match Adminer's `../adminer/static/…` links. For the default `adminer` prefix the resulting URL is unchanged. If you run Adminer under a nested prefix such as `tools/db`, assets now resolve correctly instead of pointing at a sibling directory.
- **jush is served through that same route.** The dedicated `adminer-bridge.jush` route is gone, along with the output rewriting in `head()` and `syntaxHighlighting()` that used to redirect Adminer's `../externals/jush/…` links. If you referenced `route('adminer-bridge.jush', …)` directly, use `route('adminer-bridge.static', ['file' => 'jush/…'])` instead.

Note that `adminer/static/jush` is a Git submodule in Adminer's tree, so the Composer package ships it empty — requests under that prefix are resolved against the separate `vrana/jush` package. That indirection is why this needed bridge work rather than just a version bump.

Verified against a running workbench: `default.css`, `dark.css`, `functions.js`, `editing.js`, `logo.png` and the jush CSS and modules all serve with the expected content types.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.0.1...6.0.1.0

## [6.0.0.1](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.0...6.0.0.1) - 2026-09-24

### Changed

- **`vrana/adminer` is now pinned to an exact version** (`6.0.0` instead of `^6.0`), and `vrana/jush` is constrained to patch releases only (`~3.1.0`).

This package's version scheme documents the first three segments as the Adminer release it ships, but `^6.0` let a consumer's `composer update` pull a newer Adminer minor or patch into an unchanged bridge release — so the mapping only held at the moment of install, and the bridge could silently end up running an Adminer it was never tested against.

That is not hypothetical. Adminer 6.1.0 renames `adminer/static/logo.png` to `logo.svg`, moves jush into `adminer/static/jush/`, and adds `worker.js` — enough to 404 static assets served through this bridge without a single line changing here.

Installing `magnusvin/adminer-laravel-bridge:6.0.0.*` now always gives you Adminer 6.0.0. Upstream Adminer releases reach you through a matching bridge release instead of arriving on their own.

No code or API changes — constraints and documentation only.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.0.0...6.0.0.1

## [6.0.0.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.1...6.0.0.0) - 2026-08-10

<!-- Release notes generated using configuration in .github/release.yml at 6.0.0.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.1...6.0.0.0

## [5.5.1.1](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.0...5.5.1.1) - 2026-07-30

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.1 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.0...5.5.1.1

## [5.5.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/main...5.5.1.0) - 2026-07-23

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/commits/5.5.1.0
