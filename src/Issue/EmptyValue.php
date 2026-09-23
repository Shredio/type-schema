<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

final readonly class EmptyValue extends Issue
{

	public function __construct(
		public mixed $value,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Validation;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::notEmpty($this->value);
	}

}
