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
