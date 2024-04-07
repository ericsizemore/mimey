<?php

declare(strict_types=1);

/**
 * This file is part of Esi\Mimey.
 *
 * (c) Eric Sizemore <admin@secondversion.com>
 * (c) Ricardo Boss <contact@ricardoboss.de>
 * (c) Ralph Khattar <ralph.khattar@gmail.com>
 *
 * This source file is subject to the MIT license. For the full copyright,
 * license information, and credits/acknowledgements, please view the LICENSE
 * and README files that were distributed with this source code.
 */
/**
 * Esi\Mimey is a fork of Elephox\Mimey (https://github.com/elephox-dev/mimey) which is:
 *     Copyright (c) 2022 Ricardo Boss
 * Elephox\Mimey is a fork of ralouphie/mimey (https://github.com/ralouphie/mimey) which is:
 *     Copyright (c) 2016 Ralph Khattar
 */

namespace Esi\Mimey\Tests;

use Esi\Mimey\Mapping\Builder;
use Esi\Mimey\MimeTypes;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const JSON_THROW_ON_ERROR;

/**
 * Class to test Mapping Builder.
 *
 * @internal
 */
#[CoversClass(Builder::class)]
#[UsesClass(MimeTypes::class)]
class BuilderTest extends TestCase
{
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
     * Test loading a mapping file that contains invalid JSON.
     *
     * @throws JsonException
     */
    public function testLoadInvalid(): void
    {
        $file = (string) tempnam(sys_get_temp_dir(), 'mapping_test');
        file_put_contents($file, 'invalid json');

        self::expectException(RuntimeException::class);
        Builder::load($file);
    }

    /**
     * Test saving the mapping to a file.
     *
     * @throws JsonException
     */
    public function testSave(): void
    {
        $file = (string) tempnam(sys_get_temp_dir(), 'mapping_test');

        $builder = Builder::blank();
        $builder->add('foo/one', 'one');
        $builder->add('foo/one', 'one1');
        $builder->add('foo/two', 'two');
        $builder->add('foo/two2', 'two');
        $builder->save($file);

        $json = (string) file_get_contents($file);

        $mappingIncluded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($builder->getMapping(), $mappingIncluded);

        $builder2 = Builder::load($file);

        unlink($file);

        self::assertSame($builder->getMapping(), $builder2->getMapping());
    }
}
