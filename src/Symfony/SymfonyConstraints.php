<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssueNode;
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

	public function __invoke(mixed $value, TypeContext $context): ?IssueNode
	{
		$option = $context->getOption(SymfonySchemaValidator::class);
		if ($option === null) {
			throw new LogicException(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));
		}

		$violationList = $option->validator->validate($value, $this->constraints);
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
