<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Result;

use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;

/**
 * The value could not be parsed. Carries the errors and the notices found along the way.
 */
final readonly class Failure
{

	public function __construct(
		public IssueNode $errors,
		public ?IssueNode $notices = null,
	)
	{
	}

	/**
	 * Returns Structural when at least one error is Structural (typically HTTP 400),
	 * otherwise Validation (typically HTTP 422). Notices are not taken into account.
	 */
	public function getCategory(): ErrorCategory
	{
		if ($this->errors instanceof Issue) { // fast path for union members failing on a single issue
			return $this->errors->getCategory();
		}

		foreach ($this->errors->getIssues() as $locatedIssue) {
			if ($locatedIssue->issue->getCategory() === ErrorCategory::Structural) {
				return ErrorCategory::Structural;
			}
		}

		return ErrorCategory::Validation;
	}

	/**
	 * @return non-empty-list<ErrorReport>
	 */
	public function getReports(?IssueRenderer $renderer = null, ?ErrorReportConfig $config = null): array
	{
		return ErrorReport::fromIssues($this->errors, $renderer, $config);
	}

	/**
	 * @return list<ErrorReport>
	 */
	public function getNoticeReports(?IssueRenderer $renderer = null, ?ErrorReportConfig $config = null): array
	{
		if ($this->notices === null) {
			return [];
		}

		return ErrorReport::fromIssues($this->notices, $renderer, $config);
	}

	public function withNoticesAsErrors(): self
	{
		if ($this->notices === null) {
			return $this;
		}

		return new self(IssueCollection::create([$this->errors, $this->notices]));
	}

}
