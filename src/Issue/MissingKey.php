<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

final readonly class MissingKey extends Issue
{

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Structural;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::missingField();
	}

}
