<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CompileObjectMapper
{

	/**
	 * @param non-empty-string|null $identifier
	 */
	public function __construct(
		public ?string $identifier = null,
	)
	{
	}

}
