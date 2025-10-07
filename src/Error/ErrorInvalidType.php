<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Validation\DeveloperValidationMessageFactory;

final readonly class ErrorInvalidType implements ErrorElement
{

	/** @var callable(?non-empty-string $type): string */
	private mixed $message;

	/**
	 * @param callable(?non-empty-string $type): string $message
	 */
	public function __construct(
		public TypeDefinition $definition,
		callable $message,
		private mixed $originalValue,
		private string $typeSeparator = '|',
	)
	{
		$this->message = $message;
	}

	public function withDefinition(TypeDefinition $definition): self
	{
		return new self($definition, $this->message, $this->originalValue, $this->typeSeparator);
	}

	public function getReports(array $path = []): array
	{
		return [new ErrorReport(
			($this->message)($this->definition->getUserSafeType($this->typeSeparator)),
			DeveloperValidationMessageFactory::invalidType($this->definition, $this->originalValue),
			$path,
		)];
	}

}
