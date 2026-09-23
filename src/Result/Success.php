<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Result;

use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;

/**
 * The value was parsed. Notices describe non-critical problems, e.g. extra keys that were removed.
 *
 * @template-covariant T
 */
final readonly class Success
{

	/**
	 * @param T $value
	 */
	public function __construct(
		public mixed $value,
		public ?IssueNode $notices = null,
	)
	{
	}

	public function hasNotices(): bool
	{
		return $this->notices !== null;
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

	/**
	 * @return Success<T>|Failure
	 */
	public function withNoticesAsErrors(): Success|Failure
	{
		if ($this->notices === null) {
			return $this;
		}

		return new Failure($this->notices);
	}

}
