<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

final readonly class SourceFormat
{

	/**
	 * @param non-empty-string&lowercase-string $name
	 */
	public function __construct(
		public string $name,
	)
	{
	}

	/**
	 * @param non-empty-string&lowercase-string $name
	 */
	public function is(string $name): bool
	{
		return $this->name === $name;
	}

}
