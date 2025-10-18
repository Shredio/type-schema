<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

use Shredio\TypeSchema\Types\Type;

final readonly class ClassMapperCompileHashedTargetProvider implements ClassMapperCompileTargetProvider
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
		$hash = substr(md5($className), 0, 10);
		/** @var class-string<Type<T>> $mapperClassName */
		$mapperClassName = sprintf('%s_%s', sprintf($this->mapperClassNamePattern, $shortName), $hash);
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
