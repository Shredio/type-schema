<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

interface ValidatableClassMapperProvider extends ClassMapperProvider
{

	/**
	 * @param class-string $className
	 */
	public function validate(string $className): void;

}
