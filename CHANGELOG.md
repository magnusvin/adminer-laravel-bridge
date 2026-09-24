# Release Notes

## [Unreleased](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.1...HEAD)

No unreleased changes yet. See [Versioning](README.md#versioning) for how release version numbers are chosen.

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
