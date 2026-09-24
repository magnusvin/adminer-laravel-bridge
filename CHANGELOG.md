# Release Notes

## [Unreleased](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.0...HEAD)

### Changed

- Pin `vrana/adminer` to the exact version this bridge is built against (`6.0.0` instead of `^6.0`), and constrain `vrana/jush` to patch releases only (`~3.1.0`). Previously a `composer update` could pull a newer Adminer minor or patch into an unchanged bridge release, so the first three segments of the bridge version no longer matched the Adminer version actually installed. Upstream Adminer releases now reach you only through a matching bridge release.

See [Versioning](README.md#versioning) for how release version numbers are chosen.

## [6.0.0.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.1...6.0.0.0) - 2026-08-10

<!-- Release notes generated using configuration in .github/release.yml at 6.0.0.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.1...6.0.0.0

## [5.5.1.1](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.0...5.5.1.1) - 2026-07-30

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.1 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.0...5.5.1.1

## [5.5.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/main...5.5.1.0) - 2026-07-23

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/commits/5.5.1.0
