<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
readonly class PropertyCompileOptions
{

	/**
	 * @param list<mixed> $nullValues Values that should be treated as null when mapping
	 */
	public function __construct(
		public ?bool $optional = null,
		public array $nullValues = [],
	)
	{
	}

}
