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
use Esi\Mimey\Mapping\Generator;

// Exceptions
use JsonException;
use ReflectionMethod;

// PHPUnit
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function strlen;

/**
 * Class to test Mapping Generator.
 * @internal
 */
#[CoversClass(Generator::class)]
class GeneratorTest extends TestCase
{
    public function testSpaceIndent(): void
    {
        $spaceIndent = new ReflectionMethod(Generator::class, 'spaceIndent');
        $result = $spaceIndent->invoke($spaceIndent, 0, 'test');

        self::assertStringStartsWith('    ', $result);
        self::assertSame(8, strlen($result));
    }

    /**
     * Test mapping generation with given mime.types text.
     */
    public function testGenerateMapping(): void
    {
        $generator = new Generator(
            "#ignore\tme\n" .
            "application/json\t\t\tjson\n" .
            "image/jpeg\t\t\tjpeg jpg #ignore this too\n\n" .
            "foo\tbar baz\n" .
            "qux\tbar\n"
        );

        $mapping = $generator->generateMapping();

        $expected = [
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
        ];
        self::assertSame($expected, $mapping);
    }

    /**
     * Test generating JSON from given mime.types text.
     *
     * @throws JsonException
     */
    public function testGenerateJson(): void
    {
        $generator = new Generator(
            <<<EOF
                #ignore
                application/json\tjson
                image/jpeg\tjpeg jpg
                EOF
        );

        $json    = $generator->generateJson(false);
        $minJson = $generator->generateJson();

        self::assertSame(
            <<<EOF
                {
                    "mimes": {
                        "json": [
                            "application\/json"
                        ],
                        "jpeg": [
                            "image\/jpeg"
                        ],
                        "jpg": [
                            "image\/jpeg"
                        ]
                    },
                    "extensions": {
                        "application\/json": [
                            "json"
                        ],
                        "image\/jpeg": [
                            "jpeg",
                            "jpg"
                        ]
                    }
                }
                EOF,
            $json
        );

        self::assertSame('{"mimes":{"json":["application\/json"],"jpeg":["image\/jpeg"],"jpg":["image\/jpeg"]},"extensions":{"application\/json":["json"],"image\/jpeg":["jpeg","jpg"]}}', $minJson);
    }

    /**
     * Test generating the PHP Enum from the given mime.types text.
     */
    public function testGeneratePhpEnum(): void
    {
        $generator = new Generator(
            <<<EOF
                #ignore
                application/json\tjson
                image/jpeg\tjpeg jpg
                EOF
        );

        $phpEnum = $generator->generatePhpEnum('TestMimeClass', 'TestMimeNamespace');

        self::assertSame(
            <<<EOF
                <?php

                /**
                 * @generated enum generated using bin/generate.php, please DO NOT EDIT!
                 */
                declare(strict_types=1);

                namespace TestMimeNamespace;

                use InvalidArgumentException;
                use Esi\Mimey\Interface\MimeType as MimeTypeInterface;

                enum TestMimeClass: string implements MimeTypeInterface
                {
                    case ApplicationJson = 'application/json';
                    case ImageJpeg = 'image/jpeg';

                    #[\Override]
                    public function getExtension(): string
                    {
                        return match(\$this) {
                            self::ApplicationJson => 'json',
                            self::ImageJpeg => 'jpeg',

                        };
                    }

                    #[\Override]
                    public function getValue(): string
                    {
                        return \$this->value;
                    }

                    public static function fromExtension(string \$extension): MimeType
                    {
                        \$type = self::tryFromExtension(\$extension);

                        if (\$type === null) {
                            throw new InvalidArgumentException('Unknown extension: ' . \$extension);
                        }
                        return \$type;
                    }

                    public static function tryFromExtension(string \$extension): ?MimeType
                    {
                        return match(\$extension) {
                            'json' => self::ApplicationJson,
                            'jpeg' => self::ImageJpeg,
                            'jpg' => self::ImageJpeg,

                            default => null,
                        };
                    }
                }

                EOF,
            $phpEnum
        );
    }
}
