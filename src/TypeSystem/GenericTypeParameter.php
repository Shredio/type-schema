<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\TypeSystem;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

final readonly class GenericTypeParameter
{

	public function __construct(
		public string $name,
		public GenericTypeVariance $variance = GenericTypeVariance::Invariant,
		public ?TypeNode $bound = null,
		public ?TypeNode $default = null,
	)
	{
	}

	public function toPhpDocLine(): string
	{
		$tagName = match ($this->variance) {
			GenericTypeVariance::Invariant => 'template',
			GenericTypeVariance::Covariant => 'template-covariant',
			GenericTypeVariance::Contravariant => 'template-contravariant',
		};

		$bound = $this->bound !== null ? " of {$this->bound}" : '';
		$default = $this->default !== null ? " = {$this->default}" : '';
		return trim("@{$tagName} {$this->name}{$bound}{$default}");
	}

}
