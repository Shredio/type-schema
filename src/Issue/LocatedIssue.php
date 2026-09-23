<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;


final readonly class LocatedIssue
{

	/**
	 * @param list<Path> $path
	 */
	public function __construct(
		public Issue $issue,
		public array $path = [],
	)
	{
	}

}
