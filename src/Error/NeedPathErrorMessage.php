<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Stringable;

final readonly class NeedPathErrorMessage implements ErrorElement
{

	/** @var callable(list<string|int> $path): (string|Stringable) */
	private mixed $message;

	/** @var callable(list<string|int> $path): (string|Stringable) */
	private mixed $messageForDeveloper;

	/**
	 * @param callable(list<string|int> $path): (string|Stringable) $message
	 * @param callable(list<string|int> $path): (string|Stringable) $messageForDeveloper
	 */
	public function __construct(
		callable $message,
		callable $messageForDeveloper,
	)
	{
		$this->message = $message;
		$this->messageForDeveloper = $messageForDeveloper;
	}

	public function getReports(array $path = []): array
	{
		return [new ErrorReport(($this->message)($path), ($this->messageForDeveloper)($path), $path)];
	}

}
