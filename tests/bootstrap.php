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
error_reporting(\E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Generate the mapping file.
require dirname(__DIR__) . '/bin/generate.php';
