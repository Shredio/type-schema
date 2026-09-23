<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Renderer;

use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\EmptyValue;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\ItemCountOutOfRange;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Stringable;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SymfonyIssueRenderer implements IssueRenderer
{

	public function __construct(
		private TranslatorInterface $translator,
		private string $domain = 'validators',
	)
	{
	}

	public function render(Issue $issue, ErrorReportConfig $config): string|Stringable
	{
		return match (true) {
			$issue instanceof CustomIssue => $issue->message,
			$issue instanceof InvalidType => $this->renderInvalidType($issue, $config),
			$issue instanceof MissingKey => $this->translate('This field is missing.'),
			$issue instanceof ExtraKey => $this->translate('This field was not expected.'),
			$issue instanceof EmptyValue => $this->translate('This value should not be blank.'),
			$issue instanceof NotAllowedValue => $this->translate('The value you selected is not a valid choice.'),
			$issue instanceof NumberOutOfRange => $this->renderNumberOutOfRange($issue),
			$issue instanceof ItemCountOutOfRange => $this->renderItemCountOutOfRange($issue),
			$issue instanceof InvalidDate => $this->translate('This value is not a valid date.'),
			default => $this->translate('This value is not valid.'),
		};
	}

	private function renderInvalidType(InvalidType $issue, ErrorReportConfig $config): string
	{
		$expectedType = $issue->getUserSafeExpectedType($config);
		if ($expectedType !== null) {
			return $this->translate('This value should be of type {{ type }}.', ['{{ type }}' => $expectedType]);
		}

		return $this->translate('This value is not valid.');
	}

	private function renderNumberOutOfRange(NumberOutOfRange $issue): string
	{
		$range = $issue->range;
		$exactValue = $range->getExactValue();
		if ($exactValue !== null) {
			return $this->translate(
				'This value should be equal to {{ compared_value }}.',
				['{{ compared_value }}' => (string) $exactValue],
			);
		}

		return match ($issue->decision) {
			RangeExclusiveDecision::ShouldBeGreater => $this->translate(
				'This value should be greater than {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMin()],
			),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $this->translate(
				'This value should be greater than or equal to {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMin()],
			),
			RangeExclusiveDecision::ShouldBeLess => $this->translate(
				'This value should be less than {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMax()],
			),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $this->translate(
				'This value should be less than or equal to {{ compared_value }}.',
				['{{ compared_value }}' => $range->getMax()],
			),
			default => throw new LogicException(sprintf('Unknown range decision %s.', $issue->decision->name)),
		};
	}

	private function renderItemCountOutOfRange(ItemCountOutOfRange $issue): string
	{
		$range = $issue->range;
		$exactLimit = $range->getExactValue();
		if ($exactLimit !== null) {
			return $this->translate(
				'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.',
				['{{ limit }}' => $exactLimit],
			);
		}

		return match ($issue->decision) {
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $this->translate(
				'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.',
				['{{ limit }}' => $range->getMin()],
			),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $this->translate(
				'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.',
				['{{ limit }}' => $range->getMax()],
			),
			default => throw new LogicException(sprintf('Unknown range decision %s.', $issue->decision->name)),
		};
	}

	/**
	 * @param array<string, mixed> $parameters
	 */
	private function translate(string $message, array $parameters = []): string
	{
		return $this->translator->trans($message, $parameters, $this->domain);
	}

}
