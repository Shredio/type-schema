<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

final readonly class IssueCollection implements IssueNode
{

	/**
	 * @param non-empty-list<IssueNode> $nodes
	 */
	public function __construct(
		public array $nodes,
	)
	{
	}

	/**
	 * Returns the only node directly, wraps multiple nodes into a collection.
	 *
	 * @param non-empty-list<IssueNode> $nodes
	 */
	public static function create(array $nodes): IssueNode
	{
		return isset($nodes[1]) ? new self($nodes) : $nodes[0];
	}

	public function getIssues(array $path = []): array
	{
		return array_merge(...array_map(
			static fn (IssueNode $node): array => $node->getIssues($path),
			$this->nodes,
		));
	}

}
