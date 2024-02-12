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

use Iterator;

// PHPUnit
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use ReflectionClass;

// Exceptions
use RuntimeException;

// Functions
use function dirname;
use function rename;
use function file_put_contents;
use function unlink;

/**
 * Class to test MimeTypes.
 * @internal
 */
#[CoversClass(MimeTypes::class)]
class MimeTypesTest extends TestCase
{
    /**
     * Contains the MimeTypes class instance.
     *
     * @var  MimeTypes
     */
    protected MimeTypes $mime;

    /**
     * Set up testing with needed data.
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mime = new MimeTypes([
            'mimes' => [
                'json' => ['application/json'],
                'jpeg' => ['image/jpeg'],
                'jpg'  => ['image/jpeg'],
                'bar'  => ['foo', 'qux'],
                'baz'  => ['foo'],
            ],
            'extensions' => [
                'application/json' => ['json'],
                'image/jpeg'       => ['jpeg', 'jpg'],
                'foo'              => ['bar', 'baz'],
                'qux'              => ['bar'],
            ],
        ]);
    }

    /**
     * Provides the data for testing retrieving a mime type based on extension.
     *
     * @return Iterator
     */
    public static function getMimeTypeProvider(): Iterator
    {
        yield ['application/json', 'json'];
        yield ['image/jpeg', 'jpeg'];
        yield ['image/jpeg', 'jpg'];
        yield ['foo', 'bar'];
        yield ['foo', 'baz'];
    }

    /**
     * Tests retrieving a mime type based on extension.
     */
    #[DataProvider('getMimeTypeProvider')]
    public function testGetMimeType(string $expectedMimeType, string $extension): void
    {
        self::assertSame($expectedMimeType, $this->mime->getMimeType($extension));
    }

    /**
     * Provides the data for testing retrieving an extension based on mime type.
     *
     * @return Iterator
     */
    public static function getExtensionProvider(): Iterator
    {
        yield ['json', 'application/json'];
        yield ['jpeg', 'image/jpeg'];
        yield ['bar', 'foo'];
        yield ['bar', 'qux'];
    }

    /**
     * Tests retrieving an extension based on mime type.
     */
    #[DataProvider('getExtensionProvider')]
    public function testGetExtension(string $expectedExtension, string $mimeType): void
    {
        self::assertSame($expectedExtension, $this->mime->getExtension($mimeType));
    }

    /**
     * Provides the data for testing retrieving all mime types for a given extension.
     */
    public static function getAllMimeTypesProvider(): Iterator
    {
        yield [
            ['application/json'], 'json',
        ];
        yield [
            ['image/jpeg'], 'jpeg',
        ];
        yield [
            ['image/jpeg'], 'jpg',
        ];
        yield [
            ['foo', 'qux'], 'bar',
        ];
        yield [
            ['foo'], 'baz',
        ];
    }

    /**
     * Tests retrieving all mime types for a given extension.
     *
     * @param array<string> $expectedMimeTypes
     */
    #[DataProvider('getAllMimeTypesProvider')]
    public function testGetAllMimeTypes(array $expectedMimeTypes, string $extension): void
    {
        self::assertSame($expectedMimeTypes, $this->mime->getAllMimeTypes($extension));
    }

    /**
     * Provides the data for testing retrieving all extensions for a given mime type.
     */
    public static function getAllExtensionsProvider(): Iterator
    {
        yield [
            ['json'], 'application/json',
        ];
        yield [
            ['jpeg', 'jpg'], 'image/jpeg',
        ];
        yield [
            ['bar', 'baz'], 'foo',
        ];
        yield [
            ['bar'], 'qux',
         ];
    }

    /**
     * Tests retrieving all extensions for a given mime type.
     *
     * @param array<string> $expectedExtensions
     */
    #[DataProvider('getAllExtensionsProvider')]
    public function testGetAllExtensions(array $expectedExtensions, string $mimeType): void
    {
        self::assertSame($expectedExtensions, $this->mime->getAllExtensions($mimeType));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetMimeTypeUndefined(): void
    {
        self::assertNull($this->mime->getMimeType('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetExtensionUndefined(): void
    {
        self::assertNull($this->mime->getExtension('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetAllMimeTypesUndefined(): void
    {
        self::assertSame([], $this->mime->getAllMimeTypes('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetAllExtensionsUndefined(): void
    {
        self::assertSame([], $this->mime->getAllExtensions('undefined'));
    }

    /**
     * Test built in mapping.
     */
    public function testBuiltInMapping(): void
    {
        $mimeTypes = new MimeTypes();
        self::assertSame('json', $mimeTypes->getExtension('application/json'));
        self::assertSame('application/json', $mimeTypes->getMimeType('json'));
    }

    /**
     * Test behavior based on invalid built in mapping.
     */
    public function testInvalidBuiltInMapping(): void
    {
        $original = dirname(__DIR__, 2) . '/dist/mime.types.min.json';
        $backup   = dirname(__DIR__, 2) . '/dist/mime.types.min.json.backup';

        rename($original, $backup);
        file_put_contents($original, 'invalid json');

        $reflectionClass = new ReflectionClass(MimeTypes::class);
        $reflectionClass->setStaticPropertyValue('builtIn', null);

        try {
            self::expectException(RuntimeException::class);
            new MimeTypes();
        } finally {
            unlink($original);
            rename($backup, $original);
        }
    }
}
