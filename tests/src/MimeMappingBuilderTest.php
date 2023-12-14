<?php

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.1
 * @copyright (C) 2023 Eric Sizemore
 * @license   The MIT License (MIT)
 */
namespace Esi\Mimey\Tests;

use Esi\Mimey\MimeTypes;
use Esi\Mimey\MimeMappingBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Mimey - PHP package for converting file extensions to MIME types and vice versa.
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Mimey
 * @link      https://www.secondversion.com/
 * @version   1.1.1
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
class MimeMappingBuilderTest extends TestCase
{
    /**
     * Test with a new mapping builder that has no types defined.
     */
    public function testFromEmpty(): void
    {
        $builder = MimeMappingBuilder::blank();
            $builder->add('foo/bar', 'foobar');
            $builder->add('foo/bar', 'bar');
            $builder->add('foo/baz', 'foobaz');

        $mime = new MimeTypes($builder->getMapping());

        $this->assertEquals('bar', $mime->getExtension('foo/bar'));
        $this->assertEquals(['bar', 'foobar'], $mime->getAllExtensions('foo/bar'));
        $this->assertEquals('foobaz', $mime->getExtension('foo/baz'));
        $this->assertEquals(['foobaz'], $mime->getAllExtensions('foo/baz'));
        $this->assertEquals('foo/bar', $mime->getMimeType('foobar'));
        $this->assertEquals(['foo/bar'], $mime->getAllMimeTypes('foobar'));
        $this->assertEquals('foo/bar', $mime->getMimeType('bar'));
        $this->assertEquals(['foo/bar'], $mime->getAllMimeTypes('bar'));
        $this->assertEquals('foo/baz', $mime->getMimeType('foobaz'));
        $this->assertEquals(['foo/baz'], $mime->getAllMimeTypes('foobaz'));
    }

    /**
     * Test with a mapping builder using the built-in types
     */
    public function testFromBuiltIn(): void
    {
        $builder = MimeMappingBuilder::create();
        $mime1 = new MimeTypes($builder->getMapping());

        $this->assertEquals('json', $mime1->getExtension('application/json'));
        $this->assertEquals('application/json', $mime1->getMimeType('json'));

        $builder->add('application/json', 'mycustomjson');
        $mime2 = new MimeTypes($builder->getMapping());

        $this->assertEquals('mycustomjson', $mime2->getExtension('application/json'));
        $this->assertEquals('application/json', $mime2->getMimeType('json'));

        $builder->add('application/mycustomjson', 'json');
        $mime3 = new MimeTypes($builder->getMapping());

        $this->assertEquals('mycustomjson', $mime3->getExtension('application/json'));
        $this->assertEquals('application/mycustomjson', $mime3->getMimeType('json'));
    }

    /**
     * Test appending an extension
     */
    public function testAppendExtension(): void
    {
        $builder = MimeMappingBuilder::blank();
            $builder->add('foo/bar', 'foobar');
            $builder->add('foo/bar', 'bar', false);

        $mime = new MimeTypes($builder->getMapping());
        $this->assertEquals('foobar', $mime->getExtension('foo/bar'));
    }

    /**
     * Test appending a mime
     */
    public function testAppendMime(): void
    {
        $builder = MimeMappingBuilder::blank();
            $builder->add('foo/bar', 'foobar');
            $builder->add('foo/bar2', 'foobar', true, false);

        $mime = new MimeTypes($builder->getMapping());
        $this->assertEquals('foo/bar', $mime->getMimeType('foobar'));
    }

    /**
     * Test saving the mapping to a file.
     *
     * @throws \JsonException
     */
    public function testSave(): void
    {
        /** @var string $file **/
        $file = \tempnam(\sys_get_temp_dir(), 'mapping_test');

        $builder = MimeMappingBuilder::blank();
            $builder->add('foo/one', 'one');
            $builder->add('foo/one', 'one1');
            $builder->add('foo/two', 'two');
            $builder->add('foo/two2', 'two');
        $builder->save($file);

        /** @var string $json **/
        $json = \file_get_contents($file);

        $mappingIncluded = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        $this->assertEquals($builder->getMapping(), $mappingIncluded);

        $builder2 = MimeMappingBuilder::load($file);

        \unlink($file);

        $this->assertEquals($builder->getMapping(), $builder2->getMapping());
    }

    /**
     * Test loading a mapping file that contains invalid JSON.
     *
     * @throws \JsonException
     */
    public function testLoadInvalid(): void
    {
       /** @var string $file **/
        $file = \tempnam(\sys_get_temp_dir(), 'mapping_test');
        \file_put_contents($file, 'invalid json');

        $this->expectException(RuntimeException::class);
        MimeMappingBuilder::load($file);
    }
}
