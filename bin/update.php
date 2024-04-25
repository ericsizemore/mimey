#!/usr/bin/env php
<?php

declare(strict_types=1);

$updateUrl       = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
$destinationFile = \dirname(__DIR__) . '/data/mime.types';

$mimeTypes = \file_get_contents($updateUrl);

\assert($mimeTypes !== false);

\file_put_contents($destinationFile, $mimeTypes);

echo \sprintf("Downloaded mime.types from '%s' and stored at '%s'", $updateUrl, $destinationFile) . \PHP_EOL;
echo \PHP_EOL;
