Mimey
=====

PHP package for converting file extensions to MIME types and vice versa.

[![Tests](https://github.com/elephox-dev/mimey/actions/workflows/tests.yml/badge.svg)](https://github.com/elephox-dev/mimey/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/elephox/mimey.svg)](https://packagist.org/packages/elephox/mimey)
[![Downloads per Month](https://img.shields.io/packagist/dm/elephox/mimey.svg)](https://packagist.org/packages/elephox/mimey)
[![License](https://img.shields.io/packagist/l/elephox/mimey.svg)](https://packagist.org/packages/elephox/mimey)

This package uses [httpd][]'s [mime.types][] to generate a mapping of file extension to MIME type and the other way around.

The `mime.types` file is parsed by `bin/generate.php` and converted into an optimized JSON object in `dist/mime.types.min.json`
which is then wrapped by helper class `MimeTypes`.

**Since 3.1.0:** also provides a generated PHP enum with all mime types and methods to get the extension.
Can also be used to get the enum value from an extension.

[httpd]: https://httpd.apache.org/docs/current/programs/httpd.html
[mime.types]: https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types

## Usage

```php
$mimes = new MimeTypes;

// Convert extension to MIME type:
$mimes->getMimeType('json'); // application/json

// Convert MIME type to extension:
$mimes->getExtension('application/json'); // json
```

### Using the enum

```php
$json = MimeType::ApplicationJson;
echo $json->getExtension(); // json
echo $json->value; // application/json

$html = MimeType::fromExtension('html');
echo $html->value; // text/html

MimeType::fromExtension('asdf'); // throws an InvalidArgumentException if the extension cannot be found
```

### Getting All

It's rare, but some extensions have multiple MIME types:

```php
// Get all MIME types for an extension:
$mimes->getAllMimeTypes('wmz'); // array('application/x-ms-wmz', 'application/x-msmetafile')
```

However, there are many MIME types that have multiple extensions:

```php
// Get all extensions for a MIME type:
$mimes->getAllExtensions('image/jpeg'); // array('jpeg', 'jpg', 'jpe')
```

### Custom Conversions

You can add custom conversions by changing the mapping that is given to `MimeTypes`.

There is a `MimeMappingBuilder` that can help with this:

```php
// Create a builder using the built-in conversions as the basis.
$builder = MimeMappingBuilder::create();

// Add a conversion. This conversion will take precedence over existing ones.
$builder->add('custom/mime-type', 'myextension');

$mimes = new MimeTypes($builder->getMapping());
$mimes->getMimeType('myextension'); // custom/mime-type
$mimes->getExtension('custom/mime-type'); // myextension
```

You can add as many conversions as you would like to the builder:

```php
$builder->add('custom/mime-type', 'myextension');
$builder->add('foo/bar', 'foobar');
$builder->add('foo/bar', 'fbar');
$builder->add('baz/qux', 'qux');
$builder->add('cat/qux', 'qux');
...
```

#### Optimized Custom Conversion Loading

You can optimize the loading of custom conversions by saving all conversions to a compiled PHP file as part of a build step.

```php
// Add a bunch of custom conversions.
$builder->add(...);
$builder->add(...);
$builder->add(...);
...
// Save the conversions to a cached file.
$builder->save($cache_file_path);
```

The file can then be loaded to avoid overhead of repeated `$builder->add(...)` calls:

```php
// Load the conversions from a cached file.
$builder = MimeMappingBuilder::load($cache_file_path);
$mimes = new MimeTypes($builder->getMapping());
```

## Install

Compatible with PHP >= 8.1.

```
composer require elephox/mimey
```

## Contributing

Missing a MIME type?

Open an issue or even add it yourself! The process is very easy:

1. fork this repository
2. add your MIME type to the `data/mime.types.custom` file (make sure it's properly formatted!)
3. push your changes
4. submit a pull request

After a short review and merge, the MIME type will automagically be added to the library.

If you want to, you can also run `composer generate-types` and add the changed files under `dist/` to your PR.

## Credits

This fork uses the same license as the original repository (MIT).
This repository is a fork of [ralouphie/mimey](https://github.com/ralouphie/mimey).
Thanks to them and all the contributors!
