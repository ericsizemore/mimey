<?php

namespace Esi\Mimey\Tests;

use Esi\Mimey\MimeTypes;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

class MimeTypesTest extends TestCase
{
	protected ?MimeTypes $mime = null;

	protected function setUp(): void
	{
		$this->mime = new MimeTypes([
			'mimes' => [
				'json' => ['application/json'],
				'jpeg' => ['image/jpeg'],
				'jpg' => ['image/jpeg'],
				'bar' => ['foo', 'qux'],
				'baz' => ['foo'],
			],
			'extensions' => [
				'application/json' => ['json'],
				'image/jpeg' => ['jpeg', 'jpg'],
				'foo' => ['bar', 'baz'],
				'qux' => ['bar'],
			],
		]);
	}

    /**
     * @return array<int, array<int, string>>
     */
	public static function getMimeTypeProvider(): array
	{
		return [
			['application/json', 'json'],
			['image/jpeg', 'jpeg'],
			['image/jpeg', 'jpg'],
			['foo', 'bar'],
			['foo', 'baz'],
		];
	}

	/**
	 * @dataProvider getMimeTypeProvider
	 */
	public function testGetMimeType(string $expectedMimeType, string $extension): void
	{
		$this->assertEquals($expectedMimeType, $this->mime->getMimeType($extension));
	}

    /**
     * @return array<int, array<int, string>>
     */
	public static function getExtensionProvider(): array
	{
		return [
			['json', 'application/json'],
			['jpeg', 'image/jpeg'],
			['bar', 'foo'],
			['bar', 'qux'],
		];
	}

	/**
	 * @dataProvider getExtensionProvider
	 */
	public function testGetExtension(string $expectedExtension, string $mimeType): void
	{
		$this->assertEquals($expectedExtension, $this->mime->getExtension($mimeType));
	}

    /**
     * @return array<int, array<int, string>>
     */
	public static function getAllMimeTypesProvider(): array
	{
		return [
			[
				['application/json'], 'json',
			],
			[
				['image/jpeg'], 'jpeg',
			],
			[
				['image/jpeg'], 'jpg',
			],
			[
				['foo', 'qux'], 'bar',
			],
			[
				['foo'], 'baz',
			],
		];
	}

	/**
	 * @dataProvider getAllMimeTypesProvider
     *
     * @param array<int, array<int, string>>  $expectedMimeTypes
     *
	 */
	public function testGetAllMimeTypes(array $expectedMimeTypes, string $extension): void
	{
		$this->assertEquals($expectedMimeTypes, $this->mime->getAllMimeTypes($extension));
	}

    /**
     * @return array<int, array<int, string>>
     */
	public static function getAllExtensionsProvider(): array
	{
		return [
			[
				['json'], 'application/json',
			],
			[
				['jpeg', 'jpg'], 'image/jpeg',
			],
			[
				['bar', 'baz'], 'foo',
			],
			[
				['bar'], 'qux',
			],
		];
	}

	/**
	 * @dataProvider getAllExtensionsProvider
     *
     * @param array<int, array<int, string>> $expectedExtensions
	 */
	public function testGetAllExtensions(array $expectedExtensions, string $mimeType): void
	{
		$this->assertEquals($expectedExtensions, $this->mime->getAllExtensions($mimeType));
	}

	public function testGetMimeTypeUndefined(): void
	{
		$this->assertNull($this->mime->getMimeType('undefined'));
	}

	public function testGetExtensionUndefined(): void
	{
		$this->assertNull($this->mime->getExtension('undefined'));
	}

	public function testGetAllMimeTypesUndefined(): void
	{
		$this->assertEquals([], $this->mime->getAllMimeTypes('undefined'));
	}

	public function testGetAllExtensionsUndefined(): void
	{
		$this->assertEquals([], $this->mime->getAllExtensions('undefined'));
	}

	public function testBuiltInMapping(): void
	{
		$mime = new MimeTypes();
		$this->assertEquals('json', $mime->getExtension('application/json'));
		$this->assertEquals('application/json', $mime->getMimeType('json'));
	}

	public function testInvalidBuiltInMapping(): void
	{
		$original = dirname(__DIR__, 2) . '/dist/mime.types.min.json';
		$backup = dirname(__DIR__, 2) . '/dist/mime.types.min.json.backup';
		rename($original, $backup);
		file_put_contents($original, 'invalid json');

		$property = new ReflectionProperty(MimeTypes::class, 'builtIn');
		$property->setValue(null);

		try {
			$this->expectException(RuntimeException::class);
			new MimeTypes();
		} finally {
			unlink($original);
			rename($backup, $original);
		}
	}
}
