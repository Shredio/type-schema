<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssueNode;

final readonly class SymfonyPropertyConstraints
{

	/**
	 * @param class-string $className
	 * @param non-empty-string $propertyName
	 */
	public function __construct(
		private string $className,
		private string $propertyName,
	)
	{
	}

	public function __invoke(mixed $value, TypeContext $context): ?IssueNode
	{
		$option = $context->getOption(SymfonySchemaValidator::class);
		if ($option === null) {
			throw new LogicException(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));
		}

		$violationList = $option->validator->validatePropertyValue($this->className, $this->propertyName, $value);
		$errors = [];
		foreach ($violationList as $violation) {
			$errors[] = new CustomIssue($violation->getMessage());
		}

		if ($errors === []) {
			return null;
		}

		return IssueCollection::create($errors);
	}

}
