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

use Esi\Mimey\Interface\BuilderInterface;
use RuntimeException;
use Throwable;

use function array_unique;
use function array_unshift;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Class for converting MIME types to file extensions and vice versa.
 *
 * @phpstan-import-type MimeTypeMap from \Esi\Mimey\MimeTypes
 */
class Builder implements BuilderInterface
{
    /**
     * Create a new mapping builder.
     *
     * @param MimeTypeMap $mapping An associative array containing two entries.
     *                             See `MimeTypes` constructor for details.
     */
    private function __construct(protected array $mapping) {}

    #[\Override]
    public function add(string $mime, string $extension, bool $prependExtension = true, bool $prependMime = true): void
    {
        $existingExtensions = $this->mapping['extensions'][$mime] ?? [];
        $existingMimes      = $this->mapping['mimes'][$extension] ?? [];

        if ($prependExtension) {
            array_unshift($existingExtensions, $extension);
        } else {
            $existingExtensions[] = $extension;
        }

        if ($prependMime) {
            array_unshift($existingMimes, $mime);
        } else {
            $existingMimes[] = $mime;
        }

        $this->mapping['extensions'][$mime] = array_unique($existingExtensions);
        $this->mapping['mimes'][$extension] = array_unique($existingMimes);
    }

    #[\Override]
    public function compile(bool $pretty = false): string
    {
        return json_encode($this->getMapping(), flags: JSON_THROW_ON_ERROR | ($pretty ? JSON_PRETTY_PRINT : 0));
    }

    #[\Override]
    public function getMapping(): array
    {
        return $this->mapping;
    }

    #[\Override]
    public function save(string $file, int $flags = 0, mixed $context = null): false | int
    {
        return file_put_contents($file, $this->compile(), $flags, $context);
    }

    /**
     * Create a new mapping builder that has no types defined.
     *
     * @return Builder A mapping builder with no types defined.
     */
    public static function blank(): Builder
    {
        return new self(['mimes' => [], 'extensions' => []]);
    }

    /**
     * Create a new mapping builder based on the built-in types.
     *
     * @return Builder A mapping builder with built-in types loaded.
     */
    public static function create(): Builder
    {
        return self::load(\dirname(__DIR__, 2) . '/dist/mime.types.min.json');
    }

    #[\Override]
    public static function load(string $file): Builder
    {
        try {
            /** @var string $json * */
            $json = file_get_contents($file);
            /** @var MimeTypeMap $json * */
            $json = json_decode(/** @scrutinizer ignore-type */ $json, true, flags: JSON_THROW_ON_ERROR);

            return new self($json);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Unable to parse built-in types at %s', $file), 0, $e);
        }
    }
}
