<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\TypeSystem\TypeNodeHelper;

final class TypeDefinition
{

	/** @var (callable(): TypeNode)|TypeNode */
	private mixed $typeNode;

	/**
	 * @param (callable(): TypeNode)|TypeNode $typeNode
	 */
	public function __construct(
		callable|TypeNode $typeNode,
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
		if ($this->typeNode instanceof TypeNode) {
			return $this->typeNode;
		}

		return $this->typeNode = ($this->typeNode)();
	}

}
