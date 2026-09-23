<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;


final readonly class IssuePath implements IssueNode
{

	public function __construct(
		public IssueNode $node,
		public Path $path,
	)
	{
	}

	public function withPath(Path $path): self
	{
		return new self($this->node, $path);
	}

	public function getIssues(array $path = []): array
	{
		$path[] = $this->path;

		return $this->node->getIssues($path);
	}

}
