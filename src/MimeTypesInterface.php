<?php

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

/**
 * An interface for converting between MIME types and file extensions.
 */
interface MimeTypesInterface
{
    /**
     * Get the first MIME type that matches the given file extension.
     *
     * @param   string                 $extension  The file extension to check.
     * @return  non-empty-string|null              The first matching MIME type or null if nothing matches.
     */
    public function getMimeType(string $extension): ?string;

    /**
     * Get the first file extension (without the dot) that matches the given MIME type.
     *
     * @param  string                 $mimeType  The MIME type to check.
     * @return non-empty-string|null             The first matching extension or null if nothing matches.
     */
    public function getExtension(string $mimeType): ?string;

    /**
     * Get all MIME types that match the given extension.
     *
     * @param  string                  $extension  The file extension to check.
     * @return list<non-empty-string>              An array of MIME types that match the given extension; can be empty.
     */
    public function getAllMimeTypes(string $extension): array;

    /**
     * Get all file extensions (without the dots) that match the given MIME type.
     *
     * @param  string                  $mimeType  The MIME type to check.
     * @return list<non-empty-string>             An array of file extensions that match the given MIME type; can be empty.
     */
    public function getAllExtensions(string $mimeType): array;
}
