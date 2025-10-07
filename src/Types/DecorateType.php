<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorInvalidType;

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
		if ($value instanceof ErrorElement) {
			if ($value instanceof ErrorInvalidType) {
				return $value->withDefinition($this->createDefinition($context));
			}

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
