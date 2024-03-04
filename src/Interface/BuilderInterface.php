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

namespace Esi\Mimey\Interface;

use JsonException;
use RuntimeException;

/**
 * This psalm-type looks gnarly, but it covers just about everything.
 *
 * @psalm-type MimeTypeMap = array{
 *    mimes: array<
 *        non-empty-string, list<non-empty-string>
 *    >|non-empty-array<
 *        string, array<int<0, max>, string>
 *    >,
 *    extensions: array<
 *        non-empty-string, list<non-empty-string>
 *    >|non-empty-array<
 *        string, array<int<0, max>, string>
 *    >|array<
 *        string, array<int<0, max>, string>
 *    >
 * }
 */
interface BuilderInterface
{
    /**
     * Add a conversion.
     *
     * @param string $mime              The MIME type.
     * @param string $extension         The extension.
     * @param bool   $prependExtension  Whether this should be the preferred conversion for MIME type to extension.
     * @param bool   $prependMime       Whether this should be the preferred conversion for extension to MIME type.
     */
    public function add(string $mime, string $extension, bool $prependExtension = true, bool $prependMime = true): void;

    /**
     * Retrieves the current mapping array.
     *
     * @return MimeTypeMap The mapping.
     */
    public function getMapping(): array;

    /**
     * Compile the current mapping to PHP.
     *
     * @param  bool    $pretty  Whether to pretty print the output.
     * @return string           The compiled PHP code to save to a file.
     *
     * @throws JsonException
     */
    public function compile(bool $pretty = false): string;

    /**
     * Save the current mapping to a file.
     *
     * @param  string     $file     The file to save to.
     * @param  int        $flags    Flags for `file_put_contents`.
     * @param  resource   $context  Context for `file_put_contents`.
     * @return false|int            The number of bytes that were written to the file, or false on failure.
     *
     * @throws JsonException
     */
    public function save(string $file, int $flags = 0, mixed $context = null): false | int;

    /**
     * Create a new mapping builder based on types from a file.
     *
     * @param  string            $file  The compiled PHP file to load.
     * @return BuilderInterface         A mapping builder with types loaded from a file.
     *
     * @throws RuntimeException
     */
    public static function load(string $file): BuilderInterface;
}
