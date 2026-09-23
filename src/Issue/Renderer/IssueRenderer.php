<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Renderer;

use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Stringable;

/**
 * Turns an issue into a user-facing message. Called after parsing, only for issues that are actually reported.
 */
interface IssueRenderer
{

	public function render(Issue $issue, ErrorReportConfig $config): string|Stringable;

}
