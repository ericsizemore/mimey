<?php

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.0
 * @copyright (C) 2023 Eric Sizemore
 * @license   The MIT License (MIT)
 */
namespace Esi\Mimey;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.0
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
 * Class for converting MIME types to file extensions and vice versa.
 *
 * @psalm-type MimeTypeMap = array{mimes: array<non-empty-string, list<non-empty-string>>, extensions: array<non-empty-string, list<non-empty-string>>}
 */
class MimeTypes implements MimeTypesInterface
{
    /**
     * @var ?MimeTypeMap The cached built-in mapping array.
     */
    private static ?array $builtIn = null;

    /**
     * @var ?MimeTypeMap The mapping array.
     */
    protected ?array $mapping = null;

    /**
     * Create a new mime types instance with the given mappings.
     *
     * If no mappings are defined, they will default to the ones included with this package.
     *
     * @param MimeTypeMap|null $mapping An associative array containing two entries.
     * Entry "mimes" being an associative array of extension to array of MIME types.
     * Entry "extensions" being an associative array of MIME type to array of extensions.
     * Example:
     * <code>
     * [
     *   'extensions' => [
     *     'application/json' => ['json'],
     *     'image/jpeg'       => ['jpg', 'jpeg'],
     *     ...
     *   ],
     *   'mimes' => [
     *     'json' => ['application/json'],
     *     'jpeg' => [image/jpeg'],
     *     ...
     *   ]
     * ]
     * </code>
     */
    public function __construct(?array $mapping = null)
    {
		if ($mapping === null) {
			$this->mapping = self::getBuiltIn();
		} else {
			$this->mapping = $mapping;
		}
	}

    #[Pure]
    public function getMimeType($extension): ?string
    {
        $extension = $this->cleanInput($extension);

        if (!empty($this->mapping['mimes'][$extension])) {
            return $this->mapping['mimes'][$extension][0];
        }
        return null;
    }

    #[Pure]
    public function getExtension($mimeType): ?string
    {
        $mimeType = $this->cleanInput($mimeType);

        if (!empty($this->mapping['extensions'][$mimeType])) {
            return $this->mapping['extensions'][$mimeType][0];
        }
        return null;
    }

    #[Pure]
    public function getAllMimeTypes($extension): array
    {
        $extension = $this->cleanInput($extension);

        return $this->mapping['mimes'][$extension] ?? [];
    }

    #[Pure]
    public function getAllExtensions($mimeType): array
    {
        $mimeType = $this->cleanInput($mimeType);

        return $this->mapping['extensions'][$mimeType] ?? [];
    }

    /**
     * Get the built-in mapping.
     *
     * @return MimeTypeMap The built-in mapping.
     */
    protected static function getBuiltIn(): array
    {
        if (self::$builtIn === null) {
            $builtInTypes = \dirname(__DIR__) . '/dist/mime.types.min.json';

            try {
                $json = \file_get_contents($builtInTypes);

                self::$builtIn = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                throw new RuntimeException('Failed to parse built-in mime types at $builtInTypes', 0, $e);
            }
        }
        return self::$builtIn;
    }

    /**
     * Normalize the input string using lowercase/trim.
     *
     * @param string $input The string to normalize.
     *
     * @return string The normalized string.
     */
    private function cleanInput(string $input): string
    {
        $input = \trim($input);

        if (\function_exists('\\mb_strtolower')) {
            $input = \mb_strtolower($input);
        } else {
            $input = \strtolower($input);
        }
        return $input;
    }
}
