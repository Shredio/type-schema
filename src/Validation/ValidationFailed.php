<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use Shredio\TypeSchema\Issue\IssueNode;
use Throwable;

interface ValidationFailed extends Throwable
{

	/**
	 * @return non-empty-list<IssueNode>
	 */
	public function getIssues(): array;

}
