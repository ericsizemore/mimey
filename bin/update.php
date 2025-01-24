#!/usr/bin/env php
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
$updateUrl       = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
$destinationFile = dirname(__DIR__) . '/data/mime.types';

$mimeTypes = file_get_contents($updateUrl);

assert($mimeTypes !== false);

file_put_contents($destinationFile, $mimeTypes);

echo sprintf("Downloaded mime.types from '%s' and stored at '%s'", $updateUrl, $destinationFile) . \PHP_EOL;
echo \PHP_EOL;
