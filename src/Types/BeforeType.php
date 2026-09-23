<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * @template T
 * @extends Type<T>
 */
final readonly class BeforeType extends Type
{

	/** @var callable(mixed $valueToParse, TypeContext $context): mixed */
	private mixed $fn;

	/** @var (callable(IssueNode $issues): IssueNode)|null */
	private mixed $mapIssues;

	/**
	 * @no-named-arguments
	 * @param callable(mixed $valueToParse, TypeContext $context): mixed $fn
	 * @param Type<T> $innerType
	 * @param (callable(IssueNode $issues): IssueNode)|null $mapIssues applied to both errors and notices
	 */
	public function __construct(
		callable $fn,
		private Type $innerType,
		?callable $mapIssues = null,
	)
	{
		$this->fn = $fn;
		$this->mapIssues = $mapIssues;
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$valueToParse = ($this->fn)($valueToParse, $context);
		$result = $this->innerType->parse($valueToParse, $context);
		if ($this->mapIssues === null) {
			return $result;
		}

		if ($result instanceof Failure) {
			return new Failure(
				($this->mapIssues)($result->errors),
				$result->notices === null ? null : ($this->mapIssues)($result->notices),
			);
		}

		if ($result instanceof WithNotices) {
			return $result->withNotices(($this->mapIssues)($result->notices));
		}

		return $result;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->innerType->getTypeNode($context);
	}

}
