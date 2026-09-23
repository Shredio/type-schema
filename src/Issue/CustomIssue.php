<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Stringable;

/**
 * Issue with a ready-made user message, e.g. from custom validators or Symfony constraints.
 */
final readonly class CustomIssue extends Issue
{

	public function __construct(
		public string|Stringable $message,
		public string|Stringable|null $messageForDeveloper = null,
		public ErrorCategory $category = ErrorCategory::Validation,
	)
	{
	}

	public function getCategory(): ErrorCategory
	{
		return $this->category;
	}

	public function getMessageForDeveloper(): string|Stringable
	{
		return $this->messageForDeveloper ?? $this->message;
	}

}
