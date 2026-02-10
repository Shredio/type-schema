<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Exception\LogicException;

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

	public function __invoke(mixed $value, TypeContext $context): ?ErrorElement
	{
		$option = $context->getOption(SymfonySchemaValidator::class);
		if ($option === null) {
			throw new LogicException(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));
		}

		$violationList = $option->validator->validatePropertyValue($this->className, $this->propertyName, $value);
		$errors = [];
		foreach ($violationList as $violation) {
			$errors[] = $context->errorElementFactory->createError($violation->getMessage());
		}

		if ($errors === []) {
			return null;
		}

		return $context->errorElementFactory->createCollection($errors);
	}

}
