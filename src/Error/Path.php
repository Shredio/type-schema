<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class Path
{

	public function __construct(
		public string|int $path,
		public ?IdentifiedPath $identified = null,
	)
	{
	}

	public function withPath(string|int $path): self
	{
		return new Path(
			$path,
			$this->identified,
		);
	}

}
