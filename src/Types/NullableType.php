<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorInvalidType;

/**
 * @template T
 * @extends Type<T|null>
 */
final readonly class NullableType extends Type
{

	/**
	 * @param Type<T> $type
	 * @param list<mixed> $nullValues
	 */
	public function __construct(
		private Type $type,
		private array $nullValues = [],
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		if ($valueToParse === null) {
			return null;
		}
		if (in_array($valueToParse, $this->nullValues, true)) {
			return null;
		}

		$value = $this->type->parse($valueToParse, $context);
		if (!$value instanceof ErrorElement) {
			return $value;
		}

		// if we got an error, check if it's possible that the value is null in a lenient way
		// e.g. property accepting non-empty-string|null and the value is ''
		$nullValue = $context->conversionStrategy->null($valueToParse);
		if ($nullValue === null) {
			return null;
		}

		if ($value instanceof ErrorInvalidType) {
			$value->withDefinition($this->createDefinition($context));
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new NullableTypeNode($this->type->getTypeNode($context));
	}

}
