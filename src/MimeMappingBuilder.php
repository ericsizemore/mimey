<?php

namespace Esi\Mimey;

use JetBrains\PhpStorm\Pure;
use JsonException;
use RuntimeException;
use Throwable;

/**
 * Class for converting MIME types to file extensions and vice versa.
 */
class MimeMappingBuilder
{
	/** @var array The mapping array. */
	protected array $mapping;

	/**
	 * Create a new mapping builder.
	 *
	 * @param array $mapping An associative array containing two entries. See `MimeTypes` constructor for details.
	 */
	private function __construct(array $mapping)
	{
		$this->mapping = $mapping;
	}

	/**
	 * Add a conversion.
	 *
	 * @param string $mime The MIME type.
	 * @param string $extension The extension.
	 * @param bool $prepend_extension Whether this should be the preferred conversion for MIME type to extension.
	 * @param bool $prepend_mime Whether this should be the preferred conversion for extension to MIME type.
	 */
	public function add(string $mime, string $extension, bool $prepend_extension = true, bool $prepend_mime = true): void
	{
		$existing_extensions = empty($this->mapping['extensions'][$mime]) ? [] : $this->mapping['extensions'][$mime];
		$existing_mimes = empty($this->mapping['mimes'][$extension]) ? [] : $this->mapping['mimes'][$extension];
		if ($prepend_extension) {
			array_unshift($existing_extensions, $extension);
		} else {
			$existing_extensions[] = $extension;
		}
		if ($prepend_mime) {
			array_unshift($existing_mimes, $mime);
		} else {
			$existing_mimes[] = $mime;
		}
		$this->mapping['extensions'][$mime] = array_unique($existing_extensions);
		$this->mapping['mimes'][$extension] = array_unique($existing_mimes);
	}

	/**
	 * @return array The mapping.
	 */
	public function getMapping(): array
	{
		return $this->mapping;
	}

	/**
	 * Compile the current mapping to PHP.
	 *
	 * @param bool $pretty Whether to pretty print the output.
	 *
	 * @return string The compiled PHP code to save to a file.
	 * @throws JsonException
	 */
	public function compile(bool $pretty = false): string
	{
		$mapping = $this->getMapping();

		return json_encode($mapping, flags: JSON_THROW_ON_ERROR | ($pretty ? JSON_PRETTY_PRINT : 0));
	}

	/**
	 * Save the current mapping to a file.
	 *
	 * @param string $file The file to save to.
	 * @param int $flags Flags for `file_put_contents`.
	 * @param resource $context Context for `file_put_contents`.
	 *
	 * @return int|false The number of bytes that were written to the file, or false on failure.
	 * @throws JsonException
	 */
	public function save(string $file, int $flags = 0, mixed $context = null): false|int
	{
		return file_put_contents($file, $this->compile(), $flags, $context);
	}

	/**
	 * Create a new mapping builder based on the built-in types.
	 *
	 * @return MimeMappingBuilder A mapping builder with built-in types loaded.
	 */
	public static function create(): MimeMappingBuilder
	{
		return self::load(dirname(__DIR__) . '/dist/mime.types.min.json');
	}

	/**
	 * Create a new mapping builder based on types from a file.
	 *
	 * @param string $file The compiled PHP file to load.
	 *
	 * @return MimeMappingBuilder A mapping builder with types loaded from a file.
	 */
	public static function load(string $file): MimeMappingBuilder
	{
		try {
			$json = file_get_contents($file);

			return new self(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
		} catch (Throwable $e) {
			throw new RuntimeException("Unable to parse built-in types at " . $file, 0, $e);
		}
	}

	/**
	 * Create a new mapping builder that has no types defined.
	 *
	 * @return MimeMappingBuilder A mapping builder with no types defined.
	 */
	#[Pure]
	public static function blank(): MimeMappingBuilder
	{
		return new self(['mimes' => [], 'extensions' => []]);
	}
}
