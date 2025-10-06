<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\TypeSystem;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

final readonly class GenericTypeDefinition
{

	/**
	 * @param array<string, list<int|TypeNode>> $extends
	 * @param list<GenericTypeParameter> $parameters
	 * @param array<int, list<int|null>> $parameterOffsetMapping indexed by [parameterCount]
	 */
	public function __construct(
		public array $extends = [],
		public array $parameters = [],
		public array $parameterOffsetMapping = [],
	)
	{
	}

}
