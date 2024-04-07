# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [2.0.0] - 2024-04-07

Mostly a 'restructuring' release, to set the foundation going forward. See UPGRADING.md for upgrade instructions/overview.

### Added

  * Added a new interface for `Mapping\Builder` -> `Interface\BuilderInterface`.
  * Added the `Override` attribute. to the `MimeType` enum functions `getExtension` and `getValue`.
    * Not importing the attribute, as `#[\Override]` should not cause issues in PHP < 8.3

### Changed

  * `MimeMappingBuilder` and `MimeMappingGenerator` are now `Mapping\Builder` and `Mapping\Generator`.
  * `MimeTypeInterface` and `MimeTypesInterface` are now `Interface\MimeTypeInterface` and `Interface\MimeTypesInterface`.
  * Updated PHPUnit to 11.1 along with unit tests as a result.
  * Set PHP-CS-Fixer dev dependency to `^3.52`.
  * Updated the `psalm` workflow to use the latest `psalm/psalm-github-security-scan` and `github/codeql-action/upload-sarif`.
  * Changed the header of all PHP files to be more compact.
  * Updated CHANGELOG.md to be more in line with the `Keep a Changelog` format.

### Fixed

  * Fix `Mapping\Generator::generateMapping()`'s use of `array_filter` to not rely on loose comparison.

### Removed

  * Removed `jetbrains/phpstorm-attributes` as a dependency.
  * Removed Rector from dev-dependencies.
  

## [1.2.0] - 2024-01-30

### Added

  * Added PHP-CS-Fixer to dev dependencies.
  * Added RectorPHP/Rector to dev dependencies.

### Changed

  * Fixes throughout per PHPCS (PER, PSR-2, PSR-12).
  * Changes throughout based on Rector fixes/suggestions.
  * Updated copyright/license docblocks to reduce their size, and reduce to only one.
  * Normalized composer.json and added .gitattributes.
  * Updated Mime Types.
  * Bumped minimum PHP version to 8.2.


## [1.1.1] - 2023-12-20

### Added

  * Added Scrutinizer to workflow

### Changed

  * Updating codebase based on PHPStan level 9, strict w/bleeding edge. A work in progress
  * Updated docs throughout
  * First pass and attempt at adhering to PSR-12, PSR-5, and PSR-19
  * Updated tests to use the PHPUnit CoversClass and DataProvider attributes.
  * Update composer.json and github workflows to allow PHP 8.4 into the mix.
  * Updated unit tests to use `self::` instead of `$this->` when calling PHPUnit methods
  * Pass through to add function, exception, and constant imports


## [1.1.0] - 2023-11-27

### Changed

  * Updated composer.json to remove the restriction on PHP 8.3
    * Still a minimum of PHP 8.1
  * composer.lock updated
  * data/mime.types and dist/* data updated with latest mime type data
  * Updated all calls to global PHP functions and classes, that aren't imported, with the \ prefix
  * Updated tests/src/MimeTypesTest.php to use ReflectionClass instead of ReflectionProperty to resolve a deprecation issue introduced in PHP 8.3


## [1.0.0] - 2023-07-08

Initial fork from [elephox-dev/mimey](https://github.com/elephox-dev/mimey)

### Changed

  * Updated/changed project Namespace
  * Updated tests and workflows to use PHPUnit 10.2
  * Updated workflows to use newer GitHub actions (cachev3, checkoutv3, EndBug/add-and-commit@v9)
  * Small updates here and there to code/documentation formatting/etc.
    * So essentially, this is not a new feature/update release. More of just bringing it inline with my preferences.


[2.0.0]: https://github.com/ericsizemore/mimey/releases/tag/v2.0.0
[1.2.0]: https://github.com/ericsizemore/mimey/releases/tag/v1.2.0
[1.1.1]: https://github.com/ericsizemore/mimey/releases/tag/v1.1.1
[1.1.0]: https://github.com/ericsizemore/mimey/releases/tag/v1.1.0
[1.0.0]: https://github.com/ericsizemore/mimey/releases/tag/v1.0.0