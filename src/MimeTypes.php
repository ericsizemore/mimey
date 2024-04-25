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

namespace Esi\Mimey;

use Esi\Mimey\Interface\MimeTypesInterface;
use RuntimeException;
use Throwable;

use function file_get_contents;
use function json_decode;
use function strtolower;
use function trim;

use const JSON_THROW_ON_ERROR;

/**
 * Class for converting MIME types to file extensions and vice versa.
 *
 * This psalm-type looks gnarly, but it covers just about everything.
 *
 * @phpstan-type MimeTypeMap = array{
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
class MimeTypes implements MimeTypesInterface
{
    /**
     * @var ?MimeTypeMap The mapping array.
     */
    protected ?array $mapping = null;

    /**
     * @var ?MimeTypeMap The cached built-in mapping array.
     */
    private static ?array $builtIn = null;

    /**
     * Create a new mime types instance with the given mappings.
     *
     * If no mappings are defined, they will default to the ones included with this package.
     *
     * @param null|MimeTypeMap $mapping An associative array containing two entries.
     *                                  Entry "mimes" being an associative array of extension to
     *                                  array of MIME types. Entry "extensions" being an associative
     *                                  array of MIME type to array of extensions.
     *
     *                                  Example:
     *                                  [
     *                                      'extensions' => [
     *                                          'application/json' => ['json'],
     *                                          'image/jpeg'       => ['jpg', 'jpeg'],
     *                                          ...
     *                                      ],
     *                                      'mimes' => [
     *                                          'json' => ['application/json'],
     *                                          'jpeg' => ['image/jpeg'],
     *                                          ...
     *                                      ]
     *                                  ]
     */
    public function __construct(?array $mapping = null)
    {
        $this->mapping = $mapping ?? self::getBuiltIn();
    }

    #[\Override]
    public function getAllExtensions(string $mimeType): array
    {
        return $this->mapping['extensions'][$this->cleanInput($mimeType)] ?? [];
    }

    #[\Override]
    public function getAllMimeTypes(string $extension): array
    {
        return $this->mapping['mimes'][$this->cleanInput($extension)] ?? [];
    }

    #[\Override]
    public function getExtension(string $mimeType): ?string
    {
        return $this->mapping['extensions'][$this->cleanInput($mimeType)][0] ?? null;
    }

    #[\Override]
    public function getMimeType(string $extension): ?string
    {
        return $this->mapping['mimes'][$this->cleanInput($extension)][0] ?? null;
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
                /** @var MimeTypeMap $json */
                $json = json_decode((string) file_get_contents($builtInTypes), true, flags: JSON_THROW_ON_ERROR);

                self::$builtIn = $json;
            } catch (Throwable $e) {
                throw new RuntimeException(sprintf('Failed to parse built-in mime types at %s', $builtInTypes), 0, $e);
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
        $input = trim($input);

        //@codeCoverageIgnoreStart
        return \function_exists('mb_strtolower') ? mb_strtolower($input) : strtolower($input);
        //@codeCoverageIgnoreEnd
    }
}
