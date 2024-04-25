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

namespace Esi\Mimey\Interface;

use JsonException;
use RuntimeException;

/**
 * This psalm-type looks gnarly, but it covers just about everything.
 *
 * @phpstan-import-type MimeTypeMap from \Esi\Mimey\MimeTypes
 *
 * @psalm-api
 */
interface BuilderInterface
{
    /**
     * Add a conversion.
     *
     * @param string $mime             The MIME type.
     * @param string $extension        The extension.
     * @param bool   $prependExtension Whether this should be the preferred conversion for MIME type to extension.
     * @param bool   $prependMime      Whether this should be the preferred conversion for extension to MIME type.
     */
    public function add(string $mime, string $extension, bool $prependExtension = true, bool $prependMime = true): void;

    /**
     * Compile the current mapping to PHP.
     *
     * @param bool $pretty Whether to pretty print the output.
     *
     * @throws JsonException
     *
     * @return string The compiled PHP code to save to a file.
     */
    public function compile(bool $pretty = false): string;

    /**
     * Retrieves the current mapping array.
     *
     * @return MimeTypeMap The mapping.
     */
    public function getMapping(): array;

    /**
     * Save the current mapping to a file.
     *
     * @param string        $file    The file to save to.
     * @param int           $flags   Flags for `file_put_contents`.
     * @param null|resource $context Context for `file_put_contents`.
     *
     * @throws JsonException
     *
     * @return false|int The number of bytes that were written to the file, or false on failure.
     */
    public function save(string $file, int $flags = 0, mixed $context = null): false|int;

    /**
     * Create a new mapping builder based on types from a file.
     *
     * @param string $file The compiled PHP file to load.
     *
     * @throws RuntimeException
     *
     * @return BuilderInterface A mapping builder with types loaded from a file.
     */
    public static function load(string $file): BuilderInterface;
}
