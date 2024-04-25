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

/**
 * An interface for converting between MIME types and file extensions.
 *
 * @psalm-api
 */
interface MimeTypesInterface
{
    /**
     * Get all file extensions (without the dots) that match the given MIME type.
     *
     * @param string $mimeType The MIME type to check.
     *
     * @return array<int<0, max>, string> An array of file extensions that match the given MIME type; can be empty.
     */
    public function getAllExtensions(string $mimeType): array;

    /**
     * Get all MIME types that match the given extension.
     *
     * @param string $extension The file extension to check.
     *
     * @return array<int<0, max>, string> An array of MIME types that match the given extension; can be empty.
     */
    public function getAllMimeTypes(string $extension): array;

    /**
     * Get the first file extension (without the dot) that matches the given MIME type.
     *
     * @param string $mimeType The MIME type to check.
     *
     * @return null|string The first matching extension or null if nothing matches.
     */
    public function getExtension(string $mimeType): ?string;

    /**
     * Get the first MIME type that matches the given file extension.
     *
     * @param string $extension The file extension to check.
     *
     * @return null|string The first matching MIME type or null if nothing matches.
     */
    public function getMimeType(string $extension): ?string;
}
