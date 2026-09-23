<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\Renderer\EnglishIssueRenderer;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Issue\Report\TypeSchemaErrorFormatter;
use Shredio\TypeSchema\Result\Failure;
use Throwable;

final class AssertException extends RuntimeException
{

	public function __construct(
		private readonly Failure $failure,
		private readonly IssueRenderer $issueRenderer = new EnglishIssueRenderer(),
		?Throwable $previous = null,
	)
	{
		parent::__construct('Assertion failed.', $previous);
	}

	public function getFailure(): Failure
	{
		return $this->failure;
	}

	/**
	 * @return non-empty-list<ErrorReport>
	 */
	public function getErrors(): array
	{
		return $this->failure->getReports($this->issueRenderer);
	}

	/**
	 * Resolves the overall category across all collected errors.
	 *
	 * Returns Structural when at least one error is Structural (typically HTTP 400),
	 * otherwise Validation (HTTP 422).
	 */
	public function getCategory(): ErrorCategory
	{
		return $this->failure->getCategory();
	}

	/**
	 * @return non-empty-string
	 */
	public function toPrettyString(string $listStyle = '✖ ', string $pathStyle = '→ '): string
	{
		return TypeSchemaErrorFormatter::prettyString($this->failure, $listStyle, $pathStyle);
	}

}
