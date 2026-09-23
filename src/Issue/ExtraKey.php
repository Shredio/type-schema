<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;

/**
 * Key that is not part of a closed array shape. Reported as a notice, the key is removed from the parsed value.
 */
final readonly class ExtraKey extends Issue
{

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Structural;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::extraField();
	}

}
