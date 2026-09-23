<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue;

use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;

final readonly class InvalidType extends Issue
{

	public function __construct(
		public TypeDefinition $definition,
		public mixed $value,
	)
	{
	}

	public function withDefinition(TypeDefinition $definition): self
	{
		return new self($definition, $this->value);
	}

	/**
	 * Returns the expected type in a form that is safe to show to end users, or null when it should not be shown.
	 *
	 * @return non-empty-string|null
	 */
	public function getUserSafeExpectedType(ErrorReportConfig $config): ?string
	{
		if (!$config->exposeExpectedType) {
			return null;
		}

		return $this->definition->getUserSafeType($config->typeSeparator);
	}

	public function getCategory(): ErrorCategory
	{
		return ErrorCategory::Structural;
	}

	public function getMessageForDeveloper(): string
	{
		return DeveloperValidationMessageFactory::invalidType($this->definition, $this->value);
	}

}
