<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\Validation\TypeSystem\TypeNodeHelper;

/**
 * A type is resolved to its type node lazily and only once, which keeps failures cheap to create.
 */
final class TypeDefinition
{

	/** @var (callable(): TypeNode)|TypeNode|Type<mixed> */
	private mixed $typeNode;

	/**
	 * @param (callable(): TypeNode)|TypeNode|Type<mixed> $typeNode
	 * @param TypeContext|null $context required when $typeNode is a type
	 */
	public function __construct(
		callable|TypeNode|Type $typeNode,
		private ?TypeContext $context = null,
	)
	{
		$this->typeNode = $typeNode;
	}

	public function getStringType(): string
	{
		return (string) $this->getTypeNode();
	}

	public function getSimplifiedStringType(): string
	{
		return (string) TypeNodeHelper::simplifyType($this->getTypeNode());
	}

	/**
	 * @return non-empty-string|null
	 */
	public function getUserSafeType(string $separator = '|'): ?string
	{
		$types = TypeNodeHelper::getUserSafeTypes($this->getTypeNode());
		if ($types === null) {
			return null;
		}

		return implode($separator, $types);
	}

	private function getTypeNode(): TypeNode
	{
		$typeNode = $this->typeNode;
		if ($typeNode instanceof TypeNode) {
			return $typeNode;
		}

		if ($typeNode instanceof Type) {
			if ($this->context === null) {
				throw new LogicException('The type context is required to resolve the type node of a type.');
			}

			$typeNode = $typeNode->resolveTypeNode($this->context);
			$this->context = null;

			return $this->typeNode = $typeNode;
		}

		return $this->typeNode = $typeNode();
	}

}
