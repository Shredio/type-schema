<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\NumberRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Types\Type;

interface ErrorElementFactory
{

	public function invalidType(TypeDefinition $definition, mixed $value): ErrorElement;

	public function missingField(TypeDefinition $definition): ErrorElement;

	public function extraField(TypeDefinition $definition): ErrorElement;

	public function equalTo(TypeDefinition $definition, mixed $value, string $expected): ErrorElement;

	public function invalidValue(TypeDefinition $definition, mixed $value, string $messageForDeveloper): ErrorElement;

	public function notEmpty(TypeDefinition $definition, mixed $value): ErrorMessage;

	public function numberRange(
		TypeDefinition $definition,
		mixed $value,
		NumberRange $range,
		RangeExclusiveDecision|RangeInclusiveDecision $decision,
	): ErrorElement;

	/**
	 * @param NumberInclusiveRange<int> $range
	 */
	public function itemCountRange(TypeDefinition $definition, int $count, NumberInclusiveRange $range, RangeInclusiveDecision $decision): ErrorElement;

}
