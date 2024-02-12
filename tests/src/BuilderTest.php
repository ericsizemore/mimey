<?php

declare(strict_types=1);

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @version   2.0.0
 * @copyright (C) 2023-2024 Eric Sizemore
 * @license   The MIT License (MIT)
 *
 * Copyright (C) 2023-2024 Eric Sizemore<https://www.secondversion.com/>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Esi\Mimey is a fork of Elephox\Mimey (https://github.com/elephox-dev/mimey) which is:
 *     Copyright (c) 2022 Ricardo Boss
 * Elephox\Mimey is a fork of ralouphie/mimey (https://github.com/ralouphie/mimey) which is:
 *     Copyright (c) 2016 Ralph Khattar
 */

namespace Esi\Mimey\Tests;

// Core classes
use Esi\Mimey\MimeTypes;
use Esi\Mimey\Mapping\Builder;

// PHPUnit
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

// Exceptions
use JsonException;
use RuntimeException;

// Functions & constants
use function tempnam;
use function sys_get_temp_dir;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function unlink;

use const JSON_THROW_ON_ERROR;

/**
 * Class to test Mapping Builder.
 * @internal
 */
#[CoversClass(Builder::class)]
#[UsesClass(MimeTypes::class)]
class BuilderTest extends TestCase
{
    /**
     * Test with a new mapping builder that has no types defined.
     */
    public function testFromEmpty(): void
    {
        $builder = Builder::blank();
        $builder->add('foo/bar', 'foobar');
        $builder->add('foo/bar', 'bar');
        $builder->add('foo/baz', 'foobaz');

        $mimeTypes = new MimeTypes($builder->getMapping());

        self::assertSame('bar', $mimeTypes->getExtension('foo/bar'));
        self::assertSame(['bar', 'foobar'], $mimeTypes->getAllExtensions('foo/bar'));
        self::assertSame('foobaz', $mimeTypes->getExtension('foo/baz'));
        self::assertSame(['foobaz'], $mimeTypes->getAllExtensions('foo/baz'));
        self::assertSame('foo/bar', $mimeTypes->getMimeType('foobar'));
        self::assertSame(['foo/bar'], $mimeTypes->getAllMimeTypes('foobar'));
        self::assertSame('foo/bar', $mimeTypes->getMimeType('bar'));
        self::assertSame(['foo/bar'], $mimeTypes->getAllMimeTypes('bar'));
        self::assertSame('foo/baz', $mimeTypes->getMimeType('foobaz'));
        self::assertSame(['foo/baz'], $mimeTypes->getAllMimeTypes('foobaz'));
    }

    /**
     * Test with a mapping builder using the built-in types.
     */
    public function testFromBuiltIn(): void
    {
        $builder = Builder::create();
        $mime1   = new MimeTypes($builder->getMapping());

        self::assertSame('json', $mime1->getExtension('application/json'));
        self::assertSame('application/json', $mime1->getMimeType('json'));

        $builder->add('application/json', 'mycustomjson');
        $mime2 = new MimeTypes($builder->getMapping());

        self::assertSame('mycustomjson', $mime2->getExtension('application/json'));
        self::assertSame('application/json', $mime2->getMimeType('json'));

        $builder->add('application/mycustomjson', 'json');
        $mime3 = new MimeTypes($builder->getMapping());

        self::assertSame('mycustomjson', $mime3->getExtension('application/json'));
        self::assertSame('application/mycustomjson', $mime3->getMimeType('json'));
    }

    /**
     * Test appending an extension.
     */
    public function testAppendExtension(): void
    {
        $builder = Builder::blank();
        $builder->add('foo/bar', 'foobar');
        $builder->add('foo/bar', 'bar', false);

        $mimeTypes = new MimeTypes($builder->getMapping());
        self::assertSame('foobar', $mimeTypes->getExtension('foo/bar'));
    }

    /**
     * Test appending a mime.
     */
    public function testAppendMime(): void
    {
        $builder = Builder::blank();
        $builder->add('foo/bar', 'foobar');
        $builder->add('foo/bar2', 'foobar', true, false);

        $mimeTypes = new MimeTypes($builder->getMapping());
        self::assertSame('foo/bar', $mimeTypes->getMimeType('foobar'));
    }

    /**
     * Test saving the mapping to a file.
     *
     * @throws JsonException
     */
    public function testSave(): void
    {
        /** @var string $file **/
        $file = tempnam(sys_get_temp_dir(), 'mapping_test');

        $builder = Builder::blank();
        $builder->add('foo/one', 'one');
        $builder->add('foo/one', 'one1');
        $builder->add('foo/two', 'two');
        $builder->add('foo/two2', 'two');
        $builder->save($file);

        /** @var string $json **/
        $json = file_get_contents($file);

        $mappingIncluded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($builder->getMapping(), $mappingIncluded);

        $builder2 = Builder::load($file);

        unlink($file);

        self::assertSame($builder->getMapping(), $builder2->getMapping());
    }

    /**
     * Test loading a mapping file that contains invalid JSON.
     *
     * @throws JsonException
     */
    public function testLoadInvalid(): void
    {
        /** @var string $file **/
        $file = tempnam(sys_get_temp_dir(), 'mapping_test');
        file_put_contents($file, 'invalid json');

        self::expectException(RuntimeException::class);
        Builder::load($file);
    }
}
