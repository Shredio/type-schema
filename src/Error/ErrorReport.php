<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Stringable;

final readonly class ErrorReport
{

	/**
	 * @param list<string|int> $path
	 */
	public function __construct(
		public string|Stringable $message,
		public string|Stringable $messageForDeveloper,
		public array $path = [],
	)
	{
	}

}
