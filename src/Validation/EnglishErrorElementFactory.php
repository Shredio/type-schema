<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use InvalidArgumentException;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorInvalidType;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\NumberRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;

final readonly class EnglishErrorElementFactory implements ErrorElementFactory
{

	use CommonErrorElements;

	public function invalidType(TypeDefinition $definition, mixed $value): ErrorInvalidType
	{
		return new ErrorInvalidType($definition, function (?string $userType): string {
			if ($userType !== null) {
				return sprintf('Please provide a valid %s.', $userType);
			}

			return 'The provided value is not valid.';
		}, $value);
	}

	public function missingField(TypeDefinition $definition): ErrorElement
	{
		return new ErrorMessage(
			'Please provide a value for this field.',
			DeveloperValidationMessageFactory::missingField($definition),
		);
	}

	public function extraField(TypeDefinition $definition): ErrorElement
	{
		return new ErrorMessage(
			'This field is not allowed.',
			DeveloperValidationMessageFactory::extraField($definition),
		);
	}

	public function equalTo(TypeDefinition $definition, mixed $value, string $expected): ErrorElement
	{
		return new ErrorMessage(
			sprintf('Must be equal to %s.', $expected),
			DeveloperValidationMessageFactory::equalTo($definition, $value, $expected),
		);
	}

	public function invalidValue(TypeDefinition $definition, mixed $value, string $messageForDeveloper): ErrorElement
	{
		return new ErrorMessage(
			'The provided value is not valid.',
			$messageForDeveloper,
		);
	}

	/**
	 * @param list<string|int> $allowedValues
	 */
	public function valueNotInAllowedValues(TypeDefinition $definition, string|int $value, array $allowedValues): ErrorElement
	{
		return new ErrorMessage(
			'Please choose one of the allowed values.',
			DeveloperValidationMessageFactory::valueNotInAllowedValues($definition, $value, $allowedValues),
		);
	}

	public function notEmpty(TypeDefinition $definition, mixed $value): ErrorMessage
	{
		return new ErrorMessage(
			'This value must be empty.',
			DeveloperValidationMessageFactory::notEmpty($definition, $value),
		);
	}

	public function numberRange(
		TypeDefinition $definition,
		mixed $value,
		NumberRange $range,
		RangeExclusiveDecision|RangeInclusiveDecision $decision,
	): ErrorElement
	{
		$exactValue = $range->getExactValue();
		if ($exactValue !== null) {
			return $this->equalTo($definition, $value, (string) $exactValue);
		}

		$userMessage = match ($decision) {
			RangeExclusiveDecision::ShouldBeGreater => sprintf(
				'Must be greater than %s.',
				$range->getMin(),
			),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => sprintf(
				'Must be at least %s.',
				$range->getMin(),
			),
			RangeExclusiveDecision::ShouldBeLess => sprintf(
				'Must be less than %s.',
				$range->getMax(),
			),
			RangeInclusiveDecision::ShouldBeLessOrEqual => sprintf(
				'Must be at most %s.',
				$range->getMax(),
			),
			default => throw new InvalidArgumentException('Unknown RangeDecision: ' . $decision->name),
		};
		$developerMessage = DeveloperValidationMessageFactory::numberRangeDecision($definition, $value, $range, $decision);

		return new ErrorMessage($userMessage, $developerMessage);
	}

	public function itemCountRange(TypeDefinition $definition, int $count, NumberInclusiveRange $range, RangeInclusiveDecision $decision): ErrorElement
	{
		$exactLimit = $range->getExactValue();
		$developerMessage = DeveloperValidationMessageFactory::itemCountRange($definition, $count, $range, $decision);
		if ($exactLimit !== null) {
			$userMessage = $exactLimit === 1
				? 'Must contain exactly 1 item.'
				: sprintf('Must contain exactly %d items.', $exactLimit);

			return new ErrorMessage($userMessage, $developerMessage);
		}

		$userMessage = match ($decision) {
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $range->getMin() === 1
				? 'Must contain at least 1 item.'
				: sprintf('Must contain at least %d items.', $range->getMin()),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $range->getMax() === 1
				? 'Must contain at most 1 item.'
				: sprintf('Must contain at most %d items.', $range->getMax()),
			default => throw new InvalidArgumentException('Unknown RangeDecision: ' . $decision->name),
		};

		return new ErrorMessage($userMessage, $developerMessage);
	}

	public function invalidDate(mixed $value): ErrorElement
	{
		return new ErrorMessage(
			'Please provide a valid date.',
			DeveloperValidationMessageFactory::invalidDate($value),
		);
	}

}
