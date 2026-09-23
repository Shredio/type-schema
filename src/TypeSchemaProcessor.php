<?php declare(strict_types = 1);

namespace Shredio\TypeSchema;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Issue\Renderer\EnglishIssueRenderer;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Result\WithNotices;
use Shredio\TypeSchema\Types\Type;

final readonly class TypeSchemaProcessor
{

	/**
	 * @param array<class-string, object> $defaultOptions
	 */
	public function __construct(
		private ConversionStrategy $conversionStrategy,
		private ClassMapperProvider $classMapperProvider,
		private array $defaultOptions = [],
		private IssueRenderer $issueRenderer = new EnglishIssueRenderer(),
	)
	{
	}

	/**
	 * @param array<class-string, object> $defaultOptions
	 */
	public static function createDefault(
		?ConversionStrategy $conversionStrategy = null,
		?ClassMapperProvider $classMapperProvider = null,
		array $defaultOptions = [],
		?IssueRenderer $issueRenderer = null,
	): self
	{
		return new self(
			$conversionStrategy ?? ConversionStrategyFactory::strict(),
			$classMapperProvider ?? new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers()),
			$defaultOptions,
			$issueRenderer ?? new EnglishIssueRenderer(),
		);
	}

	/**
	 * Renderer used for AssertException, useful for rendering failures returned from parse() the same way.
	 */
	public function getIssueRenderer(): IssueRenderer
	{
		return $this->issueRenderer;
	}

	/**
	 * Returns true only when the value is parsed without errors and without notices.
	 *
	 * @param Type<mixed> $type
	 */
	public function matches(mixed $value, Type $type, ?TypeConfig $config = null): bool
	{
		$result = $this->parse($value, $type, $config);

		return $result instanceof Success && $result->notices === null;
	}

	/**
	 * Collects all errors. Notices (e.g. extra keys) are treated as errors.
	 *
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 *
	 * @throws AssertException
	 */
	public function process(mixed $value, Type $type, ?TypeConfig $config = null): mixed
	{
		return $this->unwrapStrictly($this->parse($value, $type, $config, true));
	}

	/**
	 * Stops at the first error. Notices (e.g. extra keys) are treated as errors.
	 *
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 *
	 * @throws AssertException
	 */
	public function processFast(mixed $value, Type $type, ?TypeConfig $config = null): mixed
	{
		return $this->unwrapStrictly($this->parse($value, $type, $config));
	}

	/**
	 * Notices do not make the parsing fail, the caller decides what to do with them.
	 *
	 * @template T
	 * @param Type<T> $type
	 * @return Success<T>|Failure
	 */
	public function parse(mixed $value, Type $type, ?TypeConfig $config = null, bool $collectErrors = false): Success|Failure
	{
		if ($config === null) {
			$context = new TypeContext(
				$this->conversionStrategy,
				$this->classMapperProvider,
				null,
				$this->defaultOptions,
				$collectErrors,
			);
		} else {
			$context = new TypeContext(
				$config->conversionStrategy ?? $this->conversionStrategy,
				$config->classMapperProvider ?? $this->classMapperProvider,
				$config->hierarchyConfig,
				array_merge($this->defaultOptions, $config->options),
				$collectErrors,
			);
		}

		$result = $type->parse($value, $context);
		if ($result instanceof Failure) {
			return $result;
		}

		if ($result instanceof WithNotices) {
			/** @var T $parsedValue */
			$parsedValue = $result->value;

			return new Success($parsedValue, $result->notices);
		}

		return new Success($result);
	}

	/**
	 * @template T
	 * @param Success<T>|Failure $result
	 * @return T
	 *
	 * @throws AssertException
	 */
	private function unwrapStrictly(Success|Failure $result): mixed
	{
		$result = $result->withNoticesAsErrors();
		if ($result instanceof Failure) {
			throw new AssertException($result, $this->issueRenderer);
		}

		return $result->value;
	}

}
