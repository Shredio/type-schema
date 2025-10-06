<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorReport;
use Throwable;

final class AssertException extends RuntimeException
{

	public function __construct(
		private readonly ErrorElement $errorElement,
		?Throwable $previous = null,
	)
	{
		parent::__construct('Assertion failed.', $previous);
	}

	/**
	 * @return non-empty-list<ErrorReport>
	 */
	public function getErrors(): array
	{
		return $this->errorElement->getReports();
	}

	/**
	 * @return non-empty-string
	 */
	public function toPrettyString(
		string $listStyle = '✖ ',
		string $pathStyle = '→ ',
	): string
	{
		$lines = [];
		foreach ($this->getErrors() as $error) {
			$line = $listStyle . $error->messageForDeveloper;
			if ($error->path !== []) {
				$pathString = implode('.', array_map(
					fn (string|int $segment): string => is_int($segment) ? sprintf('[%d]', $segment) : $this->escapeKey($segment),
					$error->path
				));
				$line .= sprintf("\n  %sat %s", $pathStyle, $pathString);
			}
			$lines[] = $line;
		}
		return implode("\n", $lines);
	}

	private function escapeKey(string $key): string
	{
		if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
			return $key;
		}
		return sprintf("'%s'", str_replace("'", "\\'", $key));
	}

}
