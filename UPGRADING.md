# UPGRADING

## 1.x to 2.x

  * `dist/MimeType.php` remains the same.
  * `Esi\Mimey\MimeTypes` (the main class) retains the same functionality, with only changes being to the interface import.

  * This upgrade will mainly only affect those who were using the `MimeMappingBuilder` or `MimeMappingGenerator` in their code, specifically.
  * Namespaces and file names/locations have changed as follows:
    * `Esi\Mimey\MimeMappingBuilder` is now `Esi\Mimey\Mapping\Builder`
      * `src/MimeMappingBuilder.php` -> `src/Mapping/Builder.php`
    * `Esi\Mimey\MimeMappingGenerator` is now `Esi\Mimey\Mapping\Generator`.
      * `src/MimeMappingGenerator.php` -> `src/Mapping/Generator.php`
    * `Esi\Mimey\MimeTypeInterface` is now `Esi\Mimey\Interface\MimeType`
      * `src/MimeTypeInterface.php` -> `src/Interface/MimeType.php`
    * `Esi\Mimey\MimeTypesInterface` is now `Esi\Mimey\Interface\MimeTypes`
      * `src/MimeTypesInterface.php` -> `src/Interface/MimeTypes.php`
  * For example. Instead of:
```php
use Esi\Mimey\MimeMappingBuilder;

$builder = MimeMappingBuilder::create();
```
  * You would use:
```php
use Esi\Mimey\Builder;

$builder = Builder::create();
```