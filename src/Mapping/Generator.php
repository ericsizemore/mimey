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

namespace Esi\Mimey\Mapping;

use Esi\Mimey\Interfaces\MimeTypeInterface;
use JsonException;
use RuntimeException;

use function array_filter;
use function array_unique;
use function array_values;
use function explode;
use function file_get_contents;
use function json_encode;
use function preg_replace;
use function sprintf;
use function str_pad;
use function str_replace;
use function trim;
use function ucfirst;
use function ucwords;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const STR_PAD_LEFT;

/**
 * Generates a mapping for use in the MimeTypes class.
 *
 * Reads text in the format of httpd's mime.types and generates a PHP array containing the mappings.
 *
 * The psalm-type looks gnarly, but it covers just about everything.
 *
 * @phpstan-type MimeTypeMap = array{
 *    mimes?: array<
 *        non-empty-string|string, list<non-empty-string>|array<int<0, max>, string>
 *    >|non-empty-array<
 *        non-falsy-string|string, non-empty-array<0, non-falsy-string>|array<int<0, max>, string>
 *    >,
 *    extensions?: array<
 *        non-empty-string, list<non-empty-string>
 *     >|non-empty-array<
 *        string|non-falsy-string, array<int<0, max>, string>|non-empty-array<0, non-falsy-string>
 *    >|array<
 *        string, array<int<0, max>, string>
 *    >
 * }
 */
class Generator
{
    /**
     * @var MimeTypeMap
     */
    protected array $mapCache = [];

    /**
     * Create a new generator instance with the given mime.types text.
     *
     * @param non-empty-string $mimeTypesText The text from the mime.types file.
     */
    public function __construct(protected readonly string $mimeTypesText) {}

    /**
     * Generate the JSON from the mapCache.
     *
     * @param bool $minify Whether to minify the generated JSON.
     *
     * @throws JsonException
     *
     * @return non-empty-string
     */
    public function generateJson(bool $minify = true): string
    {
        return json_encode($this->generateMapping(), flags: JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT));
    }

    /**
     * Read the given mime.types text and return a mapping compatible with the MimeTypes class.
     *
     * @return MimeTypeMap The mapping.
     */
    public function generateMapping(): array
    {
        if ($this->mapCache !== []) {
            return $this->mapCache;
        }

        $lines = explode("\n", $this->mimeTypesText);

        foreach ($lines as $line) {
            $line = preg_replace('~#.*~', '', $line) ?? $line;
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = array_values(array_filter(
                explode("\t", $line),
                static fn (string $value): bool => trim($value) !== ''
            ));

            $this->generateMapCache($parts);
        }

        return $this->mapCache;
    }

    /**
     * Generates the PHP Enum found in `dist`.
     *
     * @param non-empty-string $classname
     * @param non-empty-string $namespace
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function generatePhpEnum(string $classname = 'MimeType', string $namespace = 'Esi\Mimey'): string
    {
        $values = [
            '%namespace%'       => $namespace,
            '%classname%'       => $classname,
            '%interface_usage%' => $namespace !== __NAMESPACE__ ? ('use ' . MimeTypeInterface::class . ";\n") : '',
            '%cases%'           => '',
            '%type2ext%'        => '',
            '%ext2type%'        => '',
        ];

        $stubContents = (string) file_get_contents(\dirname(__DIR__, 2) . '/stubs/mimeType.php.stub');

        $mapping = $this->generateMapping();

        $nameMap = [];

        if (!isset($mapping['mimes'], $mapping['extensions'])) {
            throw new RuntimeException('Unable to generate mapping. Possibly passed malformed $mimeTypesText');
        }

        foreach ($mapping['extensions'] as $mime => $extensions) {
            $nameMap[$mime] = $this->convertMimeTypeToCaseName($mime);

            $values['%cases%'] .= sprintf(Generator::spaceIndent(4, "case %s = '%s';\n"), $nameMap[$mime], $mime);
            $values['%type2ext%'] .= sprintf(Generator::spaceIndent(12, "self::%s => '%s',\n"), $nameMap[$mime], $extensions[0]);
        }

        foreach ($mapping['mimes'] as $extension => $mimes) {
            $values['%ext2type%'] .= sprintf(Generator::spaceIndent(12, "'%s' => self::%s,\n"), $extension, $nameMap[$mimes[0]]);
        }

        return str_replace(
            array_keys($values),
            array_values($values),
            $stubContents
        );
    }

    /**
     * @param string $mimeType
     */
    protected function convertMimeTypeToCaseName(string $mimeType): string
    {
        if ($mimeType !== '') {
            $mimeType = preg_replace('/([\/\-_+.]+)/', '', ucfirst(ucwords($mimeType, '/-_+.'))) ?? $mimeType;
        }

        return $mimeType;
    }

    /**
     * Helper function for self::generateMapping().
     *
     * @param list<string> $parts
     */
    protected function generateMapCache(array $parts): void
    {
        if (\count($parts) === 2) {
            $mime       = trim($parts[0]);
            $extensions = explode(' ', $parts[1]);

            foreach ($extensions as $extension) {
                $extension = trim($extension);

                if ($mime !== '' && $extension !== '') {
                    $this->mapCache['mimes'][$extension][] = $mime;
                    $this->mapCache['extensions'][$mime][] = $extension;
                    $this->mapCache['mimes'][$extension]   = array_unique($this->mapCache['mimes'][$extension]);
                    $this->mapCache['extensions'][$mime]   = array_unique($this->mapCache['extensions'][$mime]);
                }
            }
        }
    }

    /**
     * Helper function for self::generatePhpEnum().
     */
    protected static function spaceIndent(int $spaces, string $string): string
    {
        if ($spaces <= 0) {
            $spaces = 4;
        }

        $spaces += \strlen($string);

        return str_pad($string, $spaces, ' ', STR_PAD_LEFT);
    }
}
