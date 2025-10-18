<?php declare(strict_types = 1);

namespace Shredio\TypeSchema;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\Validation\ErrorElementFactory;
use Shredio\TypeSchema\Validation\SymfonyErrorElementFactory;
use Symfony\Component\Translation\IdentityTranslator;

final readonly class TypeSchemaProcessor
{

	public function __construct(
		private ConversionStrategy $conversionStrategy,
		private ErrorElementFactory $errorElementFactory,
		private ClassMapperProvider $classMapperProvider,
	)
	{
	}

	public static function createDefault(): self
	{
		if (!class_exists(IdentityTranslator::class)) {
			throw new LogicException('You need to install symfony/translation to use the default TypeSchemaProcessor');
		}

		return new self(
			ConversionStrategyFactory::strict(),
			new SymfonyErrorElementFactory(new IdentityTranslator()),
			new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers()),
		);
	}

	/**
	 * @param Type<mixed> $type
	 */
	public function matches(mixed $value, Type $type, ?TypeConfig $config = null): bool
	{
		$return = $this->parse($value, $type, $config);
		return !$return instanceof ErrorElement;
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 *
	 * @throws AssertException
	 */
	public function process(mixed $value, Type $type, ?TypeConfig $config = null): mixed
	{
		$return = $this->parse($value, $type, $config, true);
		return $return instanceof ErrorElement ? throw new AssertException($return) : $return;
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 *
	 * @throws AssertException
	 */
	public function processFast(mixed $value, Type $type, ?TypeConfig $config = null): mixed
	{
		$return = $this->parse($value, $type, $config);
		return $return instanceof ErrorElement ? throw new AssertException($return) : $return;
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return T|ErrorElement
	 */
	public function parse(mixed $value, Type $type, ?TypeConfig $config = null, bool $collectErrors = false): mixed
	{
		if ($config === null) {
			$context = new TypeContext(
				$this->conversionStrategy,
				$this->errorElementFactory,
				$this->classMapperProvider,
				null,
				[],
				$collectErrors,
			);
		} else {
			$context = new TypeContext(
				$config->conversionStrategy ?? $this->conversionStrategy,
				$this->errorElementFactory,
				$config->classMapperProvider ?? $this->classMapperProvider,
				$config->hierarchyConfig,
				[],
				$collectErrors,
			);
		}

		return $type->parse($value, $context);
	}

}
