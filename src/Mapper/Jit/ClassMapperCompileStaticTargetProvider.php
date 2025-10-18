<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

use Shredio\TypeSchema\Types\Type;

final readonly class ClassMapperCompileStaticTargetProvider implements ClassMapperCompileTargetProvider
{

	/**
	 * @param non-empty-string $directoryPath
	 * @param non-empty-string $mapperClassNamePattern
	 */
	public function __construct(
		public string $directoryPath,
		public string $mapperClassNamePattern,
	)
	{
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ClassMapperToCompile<T>
	 */
	public function provide(string $className): ClassMapperToCompile
	{
		$shortName = $this->extractShortName($className);
		/** @var class-string<Type<T>> $mapperClassName */
		$mapperClassName = sprintf($this->mapperClassNamePattern, $shortName);
		$targetFilePath = sprintf('%s/%s.php', rtrim($this->directoryPath, '/'), $this->extractShortName($mapperClassName));

		return new ClassMapperToCompile(
			className: $className,
			mapperClassName: $mapperClassName,
			targetFilePath: $targetFilePath,
		);
	}

	private function extractShortName(string $name): string
	{
		return ($pos = strrpos($name, '\\')) === false
			? $name
			: substr($name, $pos + 1);
	}

}
