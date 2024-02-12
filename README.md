Mimey
=====

PHP package for converting file extensions to MIME types and vice versa.

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fericsizemore%2Fmimey.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fericsizemore%2Fmimey?ref=badge_shield)
[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/mimey/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/ericsizemore/mimey/build-status/develop)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/mimey/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/ericsizemore/mimey/?branch=develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/mimey/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/ericsizemore/mimey/?branch=develop)
[![PHPStan](https://github.com/ericsizemore/mimey/actions/workflows/ci.yml/badge.svg)](https://github.com/ericsizemore/mimey/actions/workflows/ci.yml)
[![Tests](https://github.com/ericsizemore/mimey/actions/workflows/tests.yml/badge.svg)](https://github.com/ericsizemore/mimey/actions/workflows/tests.yml)
[![Psalm Security Scan](https://github.com/ericsizemore/mimey/actions/workflows/psalm.yml/badge.svg)](https://github.com/ericsizemore/mimey/actions/workflows/psalm.yml)

[![Latest Stable Version](https://img.shields.io/packagist/v/esi/mimey.svg)](https://packagist.org/packages/esi/mimey)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/mimey.svg)](https://packagist.org/packages/esi/mimey)
[![License](https://img.shields.io/packagist/l/esi/mimey.svg)](https://packagist.org/packages/esi/mimey)

This package uses [httpd]'s [mime.types] to generate a mapping of file extension to MIME type and the other way around. Click here to view the changelog from their svn: [changelog]

The `mime.types` file is parsed by `bin/generate.php` and converted into an optimized JSON object in `dist/mime.types.min.json`
which is then wrapped by helper class `MimeTypes`.

Also provides a generated PHP enum with all mime types and methods to get the extension.
Can also be used to get the enum value from an extension.

[httpd]: https://httpd.apache.org/docs/current/programs/httpd.html
[mime.types]: https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
[changelog]: https://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=log

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

There is a `Mapping\Builder` that can help with this:

```php
use Esi\Mimey\Mapping\Builder;

// Create a builder using the built-in conversions as the basis.
$builder = Builder::create();

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
$builder->save($cacheFilePath);
```

The file can then be loaded to avoid overhead of repeated `$builder->add(...)` calls:

```php
// Load the conversions from a cached file.
$builder = Builder::load($cacheFilePath);
$mimes = new MimeTypes($builder->getMapping());
```

## Install

Compatible with PHP >= 8.2.

```
composer require esi/mimey
```

## Credits

This fork uses the same license as the original repository by @ralouphie (MIT).
This repository is a fork of [elephox-dev/mimey](https://github.com/elephox-dev/mimey) which itself was a fork of [ralouphie/mimey](https://github.com/ralouphie/mimey).
Thanks to them and all the contributors!

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/mimey/issues)

Issues are the quickest way to report a bug. If you find a bug or documentation error, please check the following first:

* That there is not an Issue already open concerning the bug
* That the issue has not already been addressed (within closed Issues, for example)

### Contributing

Missing a MIME type?

Open an issue or even add it yourself! The process is very easy:

1. fork this repository
2. add your MIME type to the `data/mime.types.custom` file (make sure it's properly formatted!)
3. push your changes
4. submit a pull request

After a short review and merge, the MIME type will automagically be added to the library.

If you want to, you can also run `composer generate-types` and add the changed files under `dist/` to your PR.

--

Mimey accepts contributions of code and documentation from the community. 
These contributions can be made in the form of Issues or [Pull Requests](http://help.github.com/send-pull-requests/) on the [Mimey repository](https://github.com/ericsizemore/mimey).

Mimey is licensed under the MIT license. When submitting new features or patches to Mimey, you are giving permission to license those features or patches under the MIT license.

Mimey tries to adhere to PHPStan level 9 with strict rules and bleeding edge. Please ensure any contributions do as well.

#### Guidelines

Before we look into how, here are the guidelines. If your Pull Requests fail to pass these guidelines it will be declined and you will need to re-submit when you’ve made the changes. This might sound a bit tough, but it is required for me to maintain quality of the code-base.

#### PHP Style

Please ensure all new contributions match the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guide. The project is not fully PSR-12 compatible, yet; however, to ensure the easiest transition to the coding guidelines, I would like to go ahead and request that any contributions follow them.

#### Documentation

If you change anything that requires a change to documentation then you will need to add it. New methods, parameters, changing default values, adding constants, etc are all things that will require a change to documentation. The change-log must also be updated for every change. Also PHPDoc blocks must be maintained.

##### Documenting functions/variables (PHPDoc)

Please ensure all new contributions adhere to:

* [PSR-5 PHPDoc](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md)
* [PSR-19 PHPDoc Tags](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md)

when documenting new functions, or changing existing documentation.

#### Branching

One thing at a time: A pull request should only contain one change. That does not mean only one commit, but one change - however many commits it took. The reason for this is that if you change X and Y but send a pull request for both at the same time, we might really want X but disagree with Y, meaning we cannot merge the request. Using the Git-Flow branching model you can create new branches for both of these features and send two requests.

### Author

Eric Sizemore - <admin@secondversion.com> - <https://www.secondversion.com>

### License

Mimey is licensed under the MIT License - see the `LICENSE.md` file for details

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fericsizemore%2Fmimey.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fericsizemore%2Fmimey?ref=badge_large)
