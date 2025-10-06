<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSystem\TypeNodeHelper;

final class TypeDefinition
{

	/** @var (callable(): TypeNode)|TypeNode */
	private mixed $typeNode;

	/**
	 * @param Type<mixed> $type
	 * @param callable(): TypeNode $typeNode
	 */
	public function __construct(
		public readonly Type $type,
		callable $typeNode,
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
		if ($this->typeNode instanceof TypeNode) {
			return $this->typeNode;
		}

		return $this->typeNode = ($this->typeNode)();
	}

}
