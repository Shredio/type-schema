<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\WithNotices;

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
		if ($value instanceof Failure) {
			return $this->withOwnDefinition($value, $context);
		}

		if ($value instanceof WithNotices) {
			/** @var TDecorated $innerValue */
			$innerValue = $value->value;
			$decorated = $this->decorate($innerValue, $context);
			if ($decorated instanceof Failure) {
				return new Failure($decorated->errors, $value->notices);
			}

			return $value->withValue($decorated);
		}

		return $this->decorate($value, $context);
	}

	/**
	 * @param TDecorated $value
	 * @return T|Failure
	 */
	abstract protected function decorate(mixed $value, TypeContext $context): mixed;

	/**
	 * @return Type<TDecorated>
	 */
	abstract protected function getInnerType(): Type;

}
