<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

use Shredio\TypeSchema\Types\Type;

/**
 * @template T of object
 */
final readonly class ObjectMapperToCompile
{

	/**
	 * @param class-string<T> $className
	 * @param class-string<Type<T>> $mapperClassName
	 * @param non-empty-string $targetFilePath
	 */
	public function __construct(
		public string $className,
		public string $mapperClassName,
		public string $targetFilePath,
	)
	{
	}

}
