<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Exception\LogicException;
use Symfony\Component\Validator\Constraint;

final readonly class SymfonyConstraints
{

	/**
	 * @param list<Constraint> $constraints
	 */
	public function __construct(
		private array $constraints,
	)
	{
	}

	public function __invoke(mixed $value, TypeContext $context): ?ErrorElement
	{
		$option = $context->getOption(SymfonySchemaValidator::class);
		if ($option === null) {
			throw new LogicException(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));
		}

		$violationList = $option->validator->validate($value, $this->constraints);
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
