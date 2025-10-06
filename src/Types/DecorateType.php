<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template-covariant T
 * @template TDecorated
 * @extends Type<T>
 */
abstract readonly class DecorateType extends Type
{

	final public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$value = $this->getInnerType()->parse($valueToParse, $context);
		if ($this->isError($value)) {
			return $value;
		}

		return $this->decorate($value, $context);
	}

	/**
	 * @param TDecorated $value
	 * @return T|ErrorElement
	 */
	abstract protected function decorate(mixed $value, TypeContext $context): mixed;

	/**
	 * @return Type<TDecorated>
	 */
	abstract protected function getInnerType(): Type;

}
