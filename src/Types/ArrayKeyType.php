<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

/**
 * @extends DelegateType<array-key>
 */
final readonly class ArrayKeyType extends DelegateType
{

	/**
	 * @return Type<array-key>
	 */
	protected function getCoreType(): Type
	{
		return new UnionType([
			new StringType(),
			new IntType(),
		]);
	}

}
