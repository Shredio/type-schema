<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

interface WarmableClassMapperProvider extends ClassMapperProvider
{

	/**
	 * @param class-string $className
	 */
	public function warmup(string $className, bool $forceRecompile = true): void;

}
