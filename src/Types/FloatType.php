<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\InvalidValue;
use Shredio\TypeSchema\Result\Failure;

/**
 * @extends Type<float>
 */
final readonly class FloatType extends Type
{

	public function __construct(
		private bool $allowInf = true,
		private bool $allowNan = false,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): Failure|float
	{
		$value = $context->conversionStrategy->float($valueToParse);
		if ($value === null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		if (!$this->allowNan && is_nan($value)) {
			return new Failure(new InvalidValue($value, 'NaN values are not allowed'));
		}

		if (!$this->allowInf && is_infinite($value)) {
			return new Failure(new InvalidValue($value, 'Infinite values are not allowed'));
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('float');
	}

}
