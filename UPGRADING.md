# UPGRADING

## 2.0.0 to 2.1.0

Unfortunately, when deciding to use the `Esi\Mimey\Interface` namespace to hold all library interfaces, while it has not caused any issues, `Interface` is technically a reserved keyword. To potentially prevent any issues, 2.1.0 changes this to `Esi\Mimey\Interfaces`.

This should have no affect on most users. If you are using any of the interfaces directly, just update the call to that interface. For example:

  * `Esi\Mimey\Interface\MimeTypeInterface` -> `Esi\Mimey\Interfaces\MimeTypeInterface`

## 1.x to 2.x

  * `dist/MimeType.php` remains the same.
  * `Esi\Mimey\MimeTypes` (the main class) retains the same functionality, with only changes being to the interface import.

  * This upgrade will mainly only affect those who were using the `MimeMappingBuilder` or `MimeMappingGenerator` in their code, specifically.
  * Namespaces and file names/locations have changed as follows:
    * `Esi\Mimey\MimeMappingBuilder` is now `Esi\Mimey\Mapping\Builder`
      * `src/MimeMappingBuilder.php` -> `src/Mapping/Builder.php`
    * `Esi\Mimey\MimeMappingGenerator` is now `Esi\Mimey\Mapping\Generator`.
      * `src/MimeMappingGenerator.php` -> `src/Mapping/Generator.php`
    * `Esi\Mimey\MimeTypeInterface` is now `Esi\Mimey\Interface\MimeTypeInterface`
      * `src/MimeTypeInterface.php` -> `src/Interface/MimeTypeInterface.php`
    * `Esi\Mimey\MimeTypesInterface` is now `Esi\Mimey\Interface\MimeTypesInterface`
      * `src/MimeTypesInterface.php` -> `src/Interface/MimeTypesInterface.php`
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
