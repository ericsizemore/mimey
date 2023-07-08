<?php
declare(strict_types=1);

namespace Esi\Mimey;

interface MimeTypeInterface
{
	public function getExtension(): string;

	public function getValue(): string;
}
