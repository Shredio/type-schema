<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

use Shredio\TypeSchema\Error\ErrorCategory;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorReport;
use Shredio\TypeSchema\Error\TypeSchemaErrorFormatter;
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
	 * Resolves the overall category across all collected errors.
	 *
	 * Returns Structural when at least one error is Structural (typically HTTP 400),
	 * otherwise Validation (HTTP 422).
	 */
	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::resolve($this->errorElement->getReports());
	}

	/**
	 * @return non-empty-string
	 */
	public function toPrettyString(string $listStyle = '✖ ', string $pathStyle = '→ '): string
	{
		return TypeSchemaErrorFormatter::prettyString($this->errorElement, $listStyle, $pathStyle);
	}

}
