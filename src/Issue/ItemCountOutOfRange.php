<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

final readonly class ItemCountOutOfRange extends Issue
{

	/**
	 * @param NumberInclusiveRange<int> $range
	 */
	public function __construct(
		public int $count,
		public NumberInclusiveRange $range,
		public RangeInclusiveDecision $decision,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Validation;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::itemCountRange($this->count, $this->range, $this->decision);
	}

}
