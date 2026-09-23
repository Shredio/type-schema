<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\IssueCollector;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * @template T
 * @extends Type<list<T>>
 */
final readonly class ListType extends Type
{

	/**
	 * @param Type<T> $itemType
	 */
	public function __construct(
		private Type $itemType,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$value = $context->conversionStrategy->array($valueToParse, true);
		if ($value === null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		$return = [];
		$issues = null;
		$expectedKey = 0;
		foreach ($value as $key => $item) {
			if ($key !== $expectedKey) {
				return $this->createInvalidTypeFailure($valueToParse, $context);
			}

			$expectedKey++;
			$elementValue = $this->itemType->parse($item, $context);
			if ($elementValue instanceof Failure) {
				$issues ??= new IssueCollector();
				$issues->addChild($elementValue, $key);
				if (!$context->collectErrors) {
					return $issues->createFailure();
				}

				continue;
			}

			if ($elementValue instanceof WithNotices) {
				$issues ??= new IssueCollector();
				$issues->addChild($elementValue, $key);
				$elementValue = $elementValue->value;
			}

			$return[] = $elementValue;
		}

		/** @var list<T> $parsedValue */
		$parsedValue = $return;

		return $issues === null ? $parsedValue : $issues->createResult($parsedValue);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('list'), [
			$this->itemType->getTypeNode($context),
		]);
	}

}
