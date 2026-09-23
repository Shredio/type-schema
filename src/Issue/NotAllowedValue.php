<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

final readonly class NotAllowedValue extends Issue
{

	/**
	 * @param list<string|int> $allowedValues
	 */
	public function __construct(
		public string|int $value,
		public array $allowedValues,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Validation;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::valueNotInAllowedValues($this->value, $this->allowedValues);
	}

}
