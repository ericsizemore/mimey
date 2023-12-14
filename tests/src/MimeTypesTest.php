<?php

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.1
 * @copyright (C) 2023 Eric Sizemore
 * @license   The MIT License (MIT)
 */
namespace Esi\Mimey\Tests;

use Esi\Mimey\MimeTypes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.1
 * @copyright (C) 2023 Eric Sizemore
 * @license   The MIT License (MIT)
 *
 * Copyright (C) 2023 Eric Sizemore. All rights reserved.
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
    protected function setUp(): void
    {
        $this->mime = new MimeTypes([
            'mimes' => [
                'json' => ['application/json'],
                'jpeg' => ['image/jpeg'],
                'jpg' => ['image/jpeg'],
                'bar' => ['foo', 'qux'],
                'baz' => ['foo'],
            ],
            'extensions' => [
                'application/json' => ['json'],
                'image/jpeg' => ['jpeg', 'jpg'],
                'foo' => ['bar', 'baz'],
                'qux' => ['bar'],
            ],
        ]);
    }

    /**
     * Provides the data for testing retrieving a mime type based on extension.
     *
     * @return array<int, array<int, string>>
     */
    public static function getMimeTypeProvider(): array
    {
        return [
            ['application/json', 'json'],
            ['image/jpeg', 'jpeg'],
            ['image/jpeg', 'jpg'],
            ['foo', 'bar'],
            ['foo', 'baz'],
        ];
    }

    /**
     * Tests retrieving a mime type based on extension.
     *
     * @dataProvider getMimeTypeProvider
     */
    public function testGetMimeType(string $expectedMimeType, string $extension): void
    {
        $this->assertEquals($expectedMimeType, $this->mime->getMimeType($extension));
    }

    /**
     * Provides the data for testing retrieving an extension based on mime type.
     *
     * @return array<int, array<int, string>>
     */
    public static function getExtensionProvider(): array
    {
        return [
            ['json', 'application/json'],
            ['jpeg', 'image/jpeg'],
            ['bar', 'foo'],
            ['bar', 'qux'],
        ];
    }

    /**
     * Tests retrieving an extension based on mime type.
     *
     * @dataProvider getExtensionProvider
     */
    public function testGetExtension(string $expectedExtension, string $mimeType): void
    {
        $this->assertEquals($expectedExtension, $this->mime->getExtension($mimeType));
    }

    /**
     * Provides the data for testing retrieving all mime types for a given extension.
     *
     * @return array<int, array<int, array<int, string>|string>>
     */
    public static function getAllMimeTypesProvider(): array
    {
        return [
            [
                ['application/json'], 'json',
            ],
            [
                ['image/jpeg'], 'jpeg',
            ],
            [
                ['image/jpeg'], 'jpg',
            ],
            [
                ['foo', 'qux'], 'bar',
            ],
            [
                ['foo'], 'baz',
            ],
        ];
    }

    /**
     * Tests retrieving all mime types for a given extension.
     *
     * @dataProvider getAllMimeTypesProvider
     *
     * @param array<int, array<int, array<int, string>|string>>  $expectedMimeTypes
     *
     */
    public function testGetAllMimeTypes(array $expectedMimeTypes, string $extension): void
    {
        $this->assertEquals($expectedMimeTypes, $this->mime->getAllMimeTypes($extension));
    }

    /**
     * Provides the data for testing retrieving all extensions for a given mime type.
     *
     * @return array<int, array<int, array<int, string>|string>>
     */
    public static function getAllExtensionsProvider(): array
    {
        return [
            [
                ['json'], 'application/json',
            ],
            [
                ['jpeg', 'jpg'], 'image/jpeg',
            ],
            [
                ['bar', 'baz'], 'foo',
            ],
            [
                ['bar'], 'qux',
            ],
        ];
    }

    /**
     * Tests retrieving all extensions for a given mime type.
     *
     * @dataProvider getAllExtensionsProvider
     *
     * @param array<int, array<int, array<int, string>|string>> $expectedExtensions
     */
    public function testGetAllExtensions(array $expectedExtensions, string $mimeType): void
    {
        $this->assertEquals($expectedExtensions, $this->mime->getAllExtensions($mimeType));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetMimeTypeUndefined(): void
    {
        $this->assertNull($this->mime->getMimeType('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetExtensionUndefined(): void
    {
        $this->assertNull($this->mime->getExtension('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetAllMimeTypesUndefined(): void
    {
        $this->assertEquals([], $this->mime->getAllMimeTypes('undefined'));
    }

    /**
     * Test undefined behavior.
     */
    public function testGetAllExtensionsUndefined(): void
    {
        $this->assertEquals([], $this->mime->getAllExtensions('undefined'));
    }

    /**
     * Test built in mapping.
     */
    public function testBuiltInMapping(): void
    {
        $mime = new MimeTypes();
        $this->assertEquals('json', $mime->getExtension('application/json'));
        $this->assertEquals('application/json', $mime->getMimeType('json'));
    }

    /**
     * Test behavior basedon invalid built in mapping.
     */
    public function testInvalidBuiltInMapping(): void
    {
        $original = \dirname(__DIR__, 2) . '/dist/mime.types.min.json';
        $backup = \dirname(__DIR__, 2) . '/dist/mime.types.min.json.backup';

        \rename($original, $backup);
        \file_put_contents($original, 'invalid json');

        $class = new ReflectionClass(MimeTypes::class);
        $class->setStaticPropertyValue('builtIn', null);

        try {
            $this->expectException(RuntimeException::class);
            new MimeTypes();
        } finally {
            \unlink($original);
            \rename($backup, $original);
        }
    }
}
