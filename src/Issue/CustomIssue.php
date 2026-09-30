<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Issue\Renderer\UntranslatedMessage;
use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Issue with a ready-made user message, e.g. from custom validators or Symfony constraints. A translatable
 * message is translated by a renderer that has a translator, and rendered untranslated everywhere else.
 */
final readonly class CustomIssue extends Issue
{

	public function __construct(
		public string|Stringable|TranslatableInterface $message,
		public string|Stringable|TranslatableInterface|null $messageForDeveloper = null,
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
		$message = $this->messageForDeveloper ?? $this->message;

		return $message instanceof TranslatableInterface ? UntranslatedMessage::render($message) : $message;
	}

}
