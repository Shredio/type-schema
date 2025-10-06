<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use BackedEnum;
use DateTimeInterface;
use Shredio\TypeSchema\Types\Type;

final readonly class DefaultObjectMapperProvider implements ObjectMapperProvider
{

	public function provide(string $className): ?Type
	{
		if (is_a($className, BackedEnum::class, true)) {
			return new BackedEnumMapper($className);
		}

		if (is_a($className, DateTimeInterface::class, true)) {
			return new DateTimeMapper($className); // @phpstan-ignore return.type
		}

		return null;
	}

}
