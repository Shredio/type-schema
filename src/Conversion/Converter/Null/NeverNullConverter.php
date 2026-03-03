<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Null;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverNullConverter implements NullConverter, ConstructableConverter
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function null(mixed $value): null|false
	{
		throw new LogicException(sprintf('Cannot convert to null: %s', $this->reason));
	}

	public function constructorArguments(): array
	{
		return [$this->reason];
	}

}
