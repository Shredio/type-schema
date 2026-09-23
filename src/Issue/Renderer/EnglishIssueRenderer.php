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

final readonly class EnglishIssueRenderer implements IssueRenderer
{

	public function render(Issue $issue, ErrorReportConfig $config): string|Stringable
	{
		return match (true) {
			$issue instanceof CustomIssue => $issue->message,
			$issue instanceof InvalidType => $this->renderInvalidType($issue, $config),
			$issue instanceof MissingKey => 'Please provide a value for this field.',
			$issue instanceof ExtraKey => 'This field is not allowed.',
			$issue instanceof EmptyValue => 'This value should not be blank.',
			$issue instanceof NotAllowedValue => 'Please choose one of the allowed values.',
			$issue instanceof NumberOutOfRange => $this->renderNumberOutOfRange($issue),
			$issue instanceof ItemCountOutOfRange => $this->renderItemCountOutOfRange($issue),
			$issue instanceof InvalidDate => 'Please provide a valid date.',
			default => 'The provided value is not valid.',
		};
	}

	private function renderInvalidType(InvalidType $issue, ErrorReportConfig $config): string
	{
		$expectedType = $issue->getUserSafeExpectedType($config);
		if ($expectedType !== null) {
			return sprintf('Please provide a valid %s.', $expectedType);
		}

		return 'The provided value is not valid.';
	}

	private function renderNumberOutOfRange(NumberOutOfRange $issue): string
	{
		$range = $issue->range;
		$exactValue = $range->getExactValue();
		if ($exactValue !== null) {
			return sprintf('Must be equal to %s.', $exactValue);
		}

		return match ($issue->decision) {
			RangeExclusiveDecision::ShouldBeGreater => sprintf('Must be greater than %s.', $range->getMin()),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => sprintf('Must be at least %s.', $range->getMin()),
			RangeExclusiveDecision::ShouldBeLess => sprintf('Must be less than %s.', $range->getMax()),
			RangeInclusiveDecision::ShouldBeLessOrEqual => sprintf('Must be at most %s.', $range->getMax()),
			default => throw new LogicException(sprintf('Unknown range decision %s.', $issue->decision->name)),
		};
	}

	private function renderItemCountOutOfRange(ItemCountOutOfRange $issue): string
	{
		$range = $issue->range;
		$exactLimit = $range->getExactValue();
		if ($exactLimit !== null) {
			return $exactLimit === 1
				? 'Must contain exactly 1 item.'
				: sprintf('Must contain exactly %d items.', $exactLimit);
		}

		return match ($issue->decision) {
			RangeInclusiveDecision::ShouldBeGreaterOrEqual => $range->getMin() === 1
				? 'Must contain at least 1 item.'
				: sprintf('Must contain at least %d items.', $range->getMin()),
			RangeInclusiveDecision::ShouldBeLessOrEqual => $range->getMax() === 1
				? 'Must contain at most 1 item.'
				: sprintf('Must contain at most %d items.', $range->getMax()),
			default => throw new LogicException(sprintf('Unknown range decision %s.', $issue->decision->name)),
		};
	}

}
