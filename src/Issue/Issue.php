<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Stringable;

/**
 * A single problem found while parsing, described as data. User-facing messages are produced later by an IssueRenderer.
 */
abstract readonly class Issue implements IssueNode
{

	abstract public function getCategory(): ErrorCategory;

	abstract public function getMessageForDeveloper(): string|Stringable;

	final public function getIssues(array $path = []): array
	{
		return [new LocatedIssue($this, $path)];
	}

}
