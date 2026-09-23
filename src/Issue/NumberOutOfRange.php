<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Helper\NumberRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

final readonly class NumberOutOfRange extends Issue
{

	public function __construct(
		public mixed $value,
		public NumberRange $range,
		public RangeExclusiveDecision|RangeInclusiveDecision $decision,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Validation;
	}

	public function getMessageForDeveloper(): string
	{
		$exactValue = $this->range->getExactValue();
		if ($exactValue !== null) {
			return DeveloperValidationMessageFactory::equalTo($this->value, (string) $exactValue);
		}

		return DeveloperValidationMessageFactory::numberRangeDecision($this->value, $this->range, $this->decision);
	}

}
