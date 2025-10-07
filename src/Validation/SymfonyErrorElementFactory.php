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
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SymfonyErrorElementFactory implements ErrorElementFactory
{

	public function __construct(
		private TranslatorInterface $translator,
		private string $domain = 'validators',
	)
	{
	}

	public function invalidType(TypeDefinition $definition, mixed $value): ErrorInvalidType
	{
		return new ErrorInvalidType($definition, function (?string $userType): string {
			if ($userType !== null) {
				return $this->translator->trans(
					'This value should be of type {{ type }}.',
					['{{ type }}' => $userType],
					$this->domain,
				);
			}

			return $this->translator->trans('This value is not valid.', [], $this->domain);
		}, $value);
	}

	public function missingField(TypeDefinition $definition): ErrorElement
	{
		return new ErrorMessage(
			$this->translator->trans('This field is missing.', [], $this->domain),
			DeveloperValidationMessageFactory::missingField($definition),
		);
	}

	public function extraField(TypeDefinition $definition): ErrorElement
	{
		return new ErrorMessage(
			$this->translator->trans('This field was not expected.', [], $this->domain),
			DeveloperValidationMessageFactory::extraField($definition),
		);
	}

	public function equalTo(TypeDefinition $definition, mixed $value, string $expected): ErrorElement
	{
		return new ErrorMessage(
			$this->translator->trans(
				' This value should be equal to {{ compared_value }}.',
				['compared_value' => $expected],
				$this->domain,
			),
			DeveloperValidationMessageFactory::equalTo($definition, $value, $expected),
		);
	}

	public function invalidValue(TypeDefinition $definition, mixed $value, string $messageForDeveloper): ErrorElement
	{
		return new ErrorMessage(
			$this->translator->trans('This value is not valid.', [], $this->domain),
			$messageForDeveloper,
		);
	}

	public function notEmpty(TypeDefinition $definition, mixed $value): ErrorMessage
	{
		return new ErrorMessage(
			$this->translator->trans('This value should be blank.', [], $this->domain),
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
			RangeExclusiveDecision::ShouldBeGreater => $this->translator->trans(
				'This value should be greater than {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMin()],
				$this->domain,
			),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $this->translator->trans(
				'This value should be greater than or equal to {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMin()],
				$this->domain,
			),
			RangeExclusiveDecision::ShouldBeLess => $this->translator->trans(
				'This value should be less than {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMax()],
				$this->domain,
			),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $this->translator->trans(
				'This value should be less than or equal to {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMax()],
				$this->domain,
			),
			default => throw new InvalidArgumentException('Unknown RangeDecision: ' . $decision->name), // should not happen
		};
		$developerMessage = DeveloperValidationMessageFactory::numberRangeDecision($definition, $value, $range, $decision);

		return new ErrorMessage($userMessage, $developerMessage);
	}

	public function itemCountRange(TypeDefinition $definition, int $count, NumberInclusiveRange $range, RangeInclusiveDecision $decision): ErrorElement
	{
		$exactLimit = $range->getExactValue();
		$developerMessage = DeveloperValidationMessageFactory::itemCountRange($definition, $count, $range, $decision);
		if ($exactLimit !== null) {
			$userMessage = $this->translator->trans(
				'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.',
				['{{ limit }}' => $exactLimit],
				$this->domain,
			);

			return new ErrorMessage($userMessage, $developerMessage);
		}

		$userMessage = match ($decision) {
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $this->translator->trans(
				'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.',
				['{{ limit }}' => $range->getMin()],
				$this->domain,
			),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $this->translator->trans(
				'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.',
				['{{ limit }}' => $range->getMax()],
				$this->domain,
			),
			default => throw new InvalidArgumentException(
				'Unknown RangeDecision: ' . $decision->name
			), // should not happen
		};

		return new ErrorMessage($userMessage, $developerMessage);
	}




}
