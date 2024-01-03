### 1.2.0 (Work in progress)

  * Added PHP-CS-Fixer to dev dependencies
    * Fixes throughout per PHPCS (PER, PSR-2, PSR-12)
  * Updated copyright/license docblocks to reduce their size, and reduce to only one.
  * Normalized composer.json and added .gitattributes
  * Updated Mime Types
  * Bumped minimum PHP version to 8.2

### 1.1.1 (2023-12-20)

  * Added Scrutinizer to workflow
  * Updating codebase based on PHPStan level 9, strict w/bleeding edge. A work in progress
  * Updated docs throughout
  * First pass and attempt at adhering to PSR-12, PSR-5, and PSR-19
  * Updated tests to use the PHPUnit CoversClass and DataProvider attributes.
  * Update composer.json and github workflows to allow PHP 8.4 into the mix.
  * Updated unit tests to use `self::` instead of `$this->` when calling PHPUnit methods
  * Pass through to add function, exception, and constant imports

### 1.1.0 (2023-11-27)

  * Updated composer.json to remove the restriction on PHP 8.3
    * Still a minimum of PHP 8.1
  * composer.lock updated
  * data/mime.types and dist/* data updated with latest mime type data
  * Updated all calls to global PHP functions and classes, that aren't imported, with the \ prefix
  * Updated tests/src/MimeTypesTest.php to use ReflectionClass instead of ReflectionProperty to resolve a deprecation issue introduced in PHP 8.3

### 1.0.0 (2023-07-08)

  * Initial fork from [elephox-dev/mimey](https://github.com/elephox-dev/mimey)
  * Updated/changed project Namespace
  * Updated tests and workflows to use PHPUnit 10.2
  * Updated workflows to use newer GitHub actions (cachev3, checkoutv3, EndBug/add-and-commit@v9)
  * Small updates here and there to code/documentation formatting/etc.
    * So essentially, this is not a new feature/update release. More of just bringing it inline with my preferences.
