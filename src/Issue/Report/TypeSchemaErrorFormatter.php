<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Report;

use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Result\Failure;

final readonly class TypeSchemaErrorFormatter
{

	/**
	 * Formats developer messages of the given issues. For a Failure only its errors are formatted.
	 *
	 * @return non-empty-string
	 */
	public static function prettyString(IssueNode|Failure $issues, string $listStyle = '✖ ', string $pathStyle = '→ '): string
	{
		if ($issues instanceof Failure) {
			$issues = $issues->errors;
		}

		$lines = [];
		foreach (ErrorReport::fromIssues($issues) as $report) {
			$line = $listStyle . $report->messageForDeveloper;
			$pathString = $report->toDebugPathString();
			if ($pathString !== null) {
				$line .= sprintf("\n  %sat %s", $pathStyle, $pathString);
				$identifiedPath = $report->toIdentifiedPath();
				if ($identifiedPath !== null) {
					$line .= sprintf("\n  %sfor value %s", $pathStyle, $identifiedPath);
				}
			}
			$lines[] = $line;
		}

		return implode("\n", $lines);
	}

}
