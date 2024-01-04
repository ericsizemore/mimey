<?php

declare(strict_types=1);

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @version   1.1.1
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

namespace Esi\Mimey;

// Exceptions
use JsonException;

// Functions & constants
use function preg_replace;
use function ucfirst;
use function ucwords;
use function str_replace;
use function sprintf;
use function file_get_contents;
use function dirname;
use function json_encode;
use function array_unique;
use function trim;
use function explode;
use function count;
use function array_values;
use function array_filter;

use const JSON_THROW_ON_ERROR;
use const JSON_PRETTY_PRINT;

/**
 * Generates a mapping for use in the MimeTypes class.
 *
 * Reads text in the format of httpd's mime.types and generates a PHP array containing the mappings.
 *
 * The psalm-type's looks gnarly, but it covers just about everything. Will be worked on further. It makes
 * PHPStan happy for now.
 *
 * @psalm-type MimeTypeMap = array{mimes: array<non-empty-string, list<non-empty-string>>|non-empty-array<string, array<int<0, max>, string>>, extensions: array<non-empty-string, list<non-empty-string>>|non-empty-array<string, array<int<0, max>, string>>|array<string, array<int<0, max>, string>>}
 *
 * @psalm-type MimeTypeMapTwo =  array{mimes: non-empty-array<non-falsy-string, non-empty-array<0, non-falsy-string>>, extensions?: array<string, array<int<0, max>, string>>}
 *
 * @psalm-type MimeTypeMapThree = array{mimes?: array<string, array<int<0, max>, string>>, extensions: non-empty-array<non-falsy-string, non-empty-array<0, non-falsy-string>>}
 */
class MimeMappingGenerator
{
    /**
     * @var  string  $mimeTypesText
     */
    protected string $mimeTypesText;

    /**
     * @var  MimeTypeMap|MimeTypeMapTwo|MimeTypeMapThree|array{}  $mapCache
     */
    protected array $mapCache = [];

    /**
     * Create a new generator instance with the given mime.types text.
     *
     * @param  non-empty-string  $mimeTypesText  The text from the mime.types file.
     */
    public function __construct(string $mimeTypesText)
    {
        $this->mimeTypesText = $mimeTypesText;
    }

    /**
     * Read the given mime.types text and return a mapping compatible with the MimeTypes class.
     *
     * @return  MimeTypeMap|MimeTypeMapTwo|MimeTypeMapThree|array{}  The mapping.
     */
    public function generateMapping(): array
    {
        if ($this->mapCache !== []) {
            return $this->mapCache;
        }

        $lines = explode("\n", $this->mimeTypesText);

        foreach ($lines as $line) {
            /** @var string $line **/
            $line = preg_replace('~\\#.*~', '', $line);
            $line = trim($line);

            $parts = $line !== '' ? array_values(array_filter(explode("\t", $line))) : [];

            if (count($parts) === 2) {
                $mime = trim($parts[0]);
                $extensions = explode(' ', $parts[1]);

                foreach ($extensions as $extension) {
                    $extension = trim($extension);

                    if ($mime !== '' && $extension !== '') {
                        $this->mapCache['mimes'][$extension][] = $mime;
                        $this->mapCache['extensions'][$mime][] = $extension;
                        $this->mapCache['mimes'][$extension] = array_unique($this->mapCache['mimes'][$extension]);
                        $this->mapCache['extensions'][$mime] = array_unique($this->mapCache['extensions'][$mime]);
                    }
                }
            }
        }
        return $this->mapCache;
    }

    /**
     * Generate the JSON from the mapCache.
     *
     * @param   bool              $minify  Whether to minify the generated JSON.
     * @return  non-empty-string
     *
     * @throws JsonException
     */
    public function generateJson(bool $minify = true): string
    {
        return json_encode($this->generateMapping(), flags: JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT));
    }

    /**
     * Generates the PHP Enum found in `dist`.
     *
     * @param   non-empty-string         $classname
     * @param   non-empty-string         $namespace
     * @return  non-empty-string|string
     */
    public function generatePhpEnum(string $classname = 'MimeType', string $namespace = __NAMESPACE__): string
    {
        $values = [
            'namespace'       => $namespace,
            'classname'       => $classname,
            'interface_usage' => $namespace !== __NAMESPACE__ ? ('use ' . MimeTypeInterface::class . ";\n") : '',
            'cases'           => '',
            'type2ext'        => '',
            'ext2type'        => '',
        ];

        /** @var string $stub **/
        $stub = file_get_contents(dirname(__DIR__) . '/stubs/mimeType.php.stub');

        $mapping = $this->generateMapping();
        $nameMap = [];

        foreach ($mapping['extensions'] as $mime => $extensions) { // @phpstan-ignore-line
            $nameMap[$mime] = $this->convertMimeTypeToCaseName($mime);

            $values['cases'] .= sprintf("    case %s = '%s';\n", $nameMap[$mime], $mime);
            $values['type2ext'] .= sprintf("            self::%s => '%s',\n", $nameMap[$mime], $extensions[0]);
        }

        foreach ($mapping['mimes'] as $extension => $mimes) { // @phpstan-ignore-line
            $values['ext2type'] .= sprintf("            '%s' => self::%s,\n", $extension, $nameMap[$mimes[0]]);
        }

        foreach ($values as $name => $value) {
            $stub = str_replace("%$name%", $value, $stub);
        }
        return $stub;
    }

    /**
     * @param non-empty-string|string $mimeType
     * @return non-empty-string|string
     */
    protected function convertMimeTypeToCaseName(string $mimeType): string
    {
        // @phpstan-ignore-next-line
        return preg_replace('/([\/\-_+.]+)/', '', ucfirst(ucwords($mimeType, '/-_+.')));
    }
}
