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

use Esi\Mimey\MimeTypes;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

use function file_put_contents;
use function rename;
use function unlink;

/**
 * Class to test MimeTypes.
 *
 * @internal
 */
#[CoversClass(MimeTypes::class)]
class MimeTypesTest extends TestCase
{
    /**
     * Contains the MimeTypes class instance.
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
     * Test built in mapping.
     */
    public function testBuiltInMapping(): void
    {
        $mimeTypes = new MimeTypes();
        self::assertSame('json', $mimeTypes->getExtension('application/json'));
        self::assertSame('application/json', $mimeTypes->getMimeType('json'));
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
    public function testGetAllExtensionsUndefined(): void
    {
        self::assertSame([], $this->mime->getAllExtensions('undefined'));
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
     * Test undefined behavior.
     */
    public function testGetAllMimeTypesUndefined(): void
    {
        self::assertSame([], $this->mime->getAllMimeTypes('undefined'));
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
     * Test undefined behavior.
     */
    public function testGetExtensionUndefined(): void
    {
        self::assertNull($this->mime->getExtension('undefined'));
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
     * Test undefined behavior.
     */
    public function testGetMimeTypeUndefined(): void
    {
        self::assertNull($this->mime->getMimeType('undefined'));
    }

    /**
     * Test behavior based on invalid built in mapping.
     */
    public function testInvalidBuiltInMapping(): void
    {
        $original = \dirname(__DIR__, 2) . '/dist/mime.types.min.json';
        $backup   = \dirname(__DIR__, 2) . '/dist/mime.types.min.json.backup';

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
     * Provides the data for testing retrieving an extension based on mime type.
     */
    public static function getExtensionProvider(): Iterator
    {
        yield ['json', 'application/json'];
        yield ['jpeg', 'image/jpeg'];
        yield ['bar', 'foo'];
        yield ['bar', 'qux'];
    }

    /**
     * Provides the data for testing retrieving a mime type based on extension.
     */
    public static function getMimeTypeProvider(): Iterator
    {
        yield ['application/json', 'json'];
        yield ['image/jpeg', 'jpeg'];
        yield ['image/jpeg', 'jpg'];
        yield ['foo', 'bar'];
        yield ['foo', 'baz'];
    }
}
