<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;


final readonly class InvalidValue extends Issue
{

	public function __construct(
		public mixed $value,
		public string $messageForDeveloper,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Validation;
	}

	public function getMessageForDeveloper(): string
	{
		return $this->messageForDeveloper;
	}

}
