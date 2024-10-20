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

use Esi\Mimey\Mapping\Generator;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Class to test Mapping Generator.
 *
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(Generator::class)]
class GeneratorTest extends TestCase
{
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
                 *
                 * @codeCoverageIgnore
                 */
                declare(strict_types=1);

                namespace TestMimeNamespace;

                use InvalidArgumentException;
                use Esi\Mimey\Interfaces\MimeTypeInterface;

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

    /**
     * Test generating the PHP Enum from the given mime.types text.
     */
    public function testGeneratePhpEnumDefault(): void
    {
        $generator = new Generator(
            <<<EOF
                #ignore
                application/json\tjson
                image/jpeg\tjpeg jpg
                EOF
        );

        $phpEnum = $generator->generatePhpEnum();

        self::assertSame(
            <<<EOF
                <?php

                /**
                 * @generated enum generated using bin/generate.php, please DO NOT EDIT!
                 *
                 * @codeCoverageIgnore
                 */
                declare(strict_types=1);

                namespace Esi\Mimey;

                use InvalidArgumentException;
                use Esi\Mimey\Interfaces\MimeTypeInterface;

                enum MimeType: string implements MimeTypeInterface
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

    /**
     * Test generating the PHP Enum when given invalid mime.types text.
     */
    public function testGeneratePhpEnumInvalid(): void
    {
        $generator = new Generator(
            <<<EOF
                #ignore
                #application/json\tjson
                #image/jpeg\tjpeg jpg
                EOF
        );

        $this->expectException(RuntimeException::class);
        $generator->generatePhpEnum('TestMimeClass', 'TestMimeNamespace');
    }

    /**
     * Test generating the PHP Enum when given invalid mime.types text.
     *
     * @psalm-suppress InvalidArgument
     */
    public function testGeneratePhpEnumInvalidNoParam(): void
    {
        $generator = new Generator('');

        $this->expectException(RuntimeException::class);
        $generator->generatePhpEnum('TestMimeClass', 'TestMimeNamespace');
    }

    /**
     * Test generating the PHP Enum when given invalid mime.types text.
     */
    public function testGeneratePhpEnumInvalidNoTab(): void
    {
        $generator = new Generator(
            <<<'EOF'
                #ignore
                application/jsonjson
                image/jpegjpegjpg
                EOF
        );

        $this->expectException(RuntimeException::class);
        $generator->generatePhpEnum('TestMimeClass', 'TestMimeNamespace');
    }

    public function testSpaceIndent(): void
    {
        $spaceIndent = new ReflectionMethod(Generator::class, 'spaceIndent');

        /** @var string $result */
        $result = $spaceIndent->invoke($spaceIndent, 0, 'test');

        self::assertStringStartsWith('    ', $result);
        self::assertSame(8, \strlen($result));
    }
}
