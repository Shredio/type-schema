<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;


/**
 * Node of an issue tree. Leaves are issues, inner nodes attach a path segment or group several nodes.
 */
interface IssueNode
{

	/**
	 * @param list<Path> $path
	 * @return non-empty-list<LocatedIssue>
	 */
	public function getIssues(array $path = []): array;

}
