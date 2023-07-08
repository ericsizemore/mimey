<?php

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.0.0
 * @copyright (C) 2023 Eric Sizemore
 * @license   The MIT License (MIT)
 */
namespace Esi\Mimey;

use JsonException;

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.0.0
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

/**
 * Generates a mapping for use in the MimeTypes class.
 *
 * Reads text in the format of httpd's mime.types and generates a PHP array containing the mappings.
 *
 * @psalm-type MimeTypeMap = array{mimes: array<non-empty-string, list<non-empty-string>>, extensions: array<non-empty-string, list<non-empty-string>>}
 */
class MimeMappingGenerator
{
    protected string $mimeTypesText;
    protected ?array $mapCache = null;

    /**
     * Create a new generator instance with the given mime.types text.
     *
     * @param non-empty-string $mime_types_text The text from the mime.types file.
     */
    public function __construct(string $mimeTypesText)
    {
        $this->mimeTypesText = $mimeTypesText;
    }

    /**
     * Read the given mime.types text and return a mapping compatible with the MimeTypes class.
     *
     * @return MimeTypeMap The mapping.
     */
    public function generateMapping(): array
    {
        if ($this->mapCache !== null) {
            return $this->mapCache;
        }

        $this->mapCache = [];

        $lines = explode("\n", $this->mimeTypesText);

        foreach ($lines AS $line) {
            $line = trim(preg_replace('~\\#.*~', '', $line));
            $parts = $line ? array_values(array_filter(explode("\t", $line))) : [];

            if (count($parts) === 2) {
                $mime = trim($parts[0]);
                $extensions = explode(' ', $parts[1]);

                foreach ($extensions AS $extension) {
                    $extension = trim($extension);

                    if ($mime AND $extension) {
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
     * @return non-empty-string
     * @throws JsonException
     */
    public function generateJson(bool $minify = true): string
    {
        return json_encode($this->generateMapping(), JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT));
    }

    /**
     * @param non-empty-string $classname
     * @param non-empty-string $namespace
     * @return non-empty-string
     */
    public function generatePhpEnum(string $classname = 'MimeType', string $namespace = __NAMESPACE__): string
    {
        $values = [
            'namespace'       => $namespace,
            'classname'       => $classname,
            'interface_usage' => $namespace !== __NAMESPACE__ ? ("use " . MimeTypeInterface::class . ";\n") : '',
            'cases'           => '',
            'type2ext'        => '',
            'ext2type'        => '',
        ];

        $stub = file_get_contents(dirname(__DIR__) . '/stubs/mimeType.php.stub');

        $mapping = $this->generateMapping();
        $nameMap = [];

        foreach ($mapping['extensions'] AS $mime => $extensions) {
            $nameMap[$mime] = $this->convertMimeTypeToCaseName($mime);

            $values['cases'] .= sprintf("\tcase %s = '%s';\n", $nameMap[$mime], $mime);
            $values['type2ext'] .= sprintf("\t\t\tself::%s => '%s',\n", $nameMap[$mime], $extensions[0]);
        }

        foreach ($mapping['mimes'] AS $extension => $mimes) {
            $values['ext2type'] .= sprintf("\t\t\t'%s' => self::%s,\n", $extension, $nameMap[$mimes[0]]);
        }

        foreach ($values AS $name => $value) {
            $stub = str_replace("%$name%", $value, $stub);
        }
        return $stub;
    }

    protected function convertMimeTypeToCaseName(string $mimeType): string
    {
        return preg_replace('/([\/\-_+.]+)/', '', ucfirst(ucwords($mimeType, '/-_+.')));
    }
}
