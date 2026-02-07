<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Stringable;

final readonly class ErrorMessage implements ErrorElement
{

	public function __construct(
		public string|Stringable $message,
		public string|Stringable $messageForDeveloper,
	)
	{
	}

	public function getReports(array $path = [], ?ErrorReportConfig $config = null): array
	{
		return [new ErrorReport($this->message, $this->messageForDeveloper, $path)];
	}

}
