<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverStringConverter implements StringConverter, ConstructableConverter
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function string(mixed $value): ?string
	{
		throw new LogicException(sprintf('Cannot convert to string: %s', $this->reason));
	}

	public function constructorArguments(): array
	{
		return [$this->reason];
	}

}