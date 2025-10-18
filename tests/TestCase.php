<?php declare(strict_types = 1);

namespace Tests;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use Shredio\TypeSchema\Validation\SymfonyErrorElementFactory;
use Symfony\Component\Translation\IdentityTranslator;
use Tests\Common\TestConversionStrategy;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

	protected function getProcessor(): TypeSchemaProcessor
	{
		return new TypeSchemaProcessor(
			new TestConversionStrategy(),
			new SymfonyErrorElementFactory(new IdentityTranslator()),
			new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers()),
		);
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 */
	protected function validStrictParse(Type $type, mixed $value): mixed
	{
		$ret = $this->getProcessor()->parse($value, $type);
		$this->assertNotInstanceOf(ErrorElement::class, $ret);

		return $ret;
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return T
	 */
	protected function validLenientParse(Type $type, mixed $value): mixed
	{
		$ret = $this->getProcessor()->parse($value, $type, new TypeConfig(ConversionStrategyFactory::lenient()));
		$this->assertNotInstanceOf(ErrorElement::class, $ret);

		return $ret;
	}

	/**
	 * @return Type<mixed>
	 */
	protected function createLargeTypeSchema(): Type
	{
		$t = TypeSchema::get();
		return $t->arrayShape([
			'id' => $t->int(),
			'name' => $t->string(),
			'email' => $t->string(),
			'age' => $t->int(),
			'active' => $t->bool(),
			'score' => $t->float(),
			'tags' => $t->list($t->string()),
			'address' => $t->arrayShape([
				'street' => $t->string(),
				'number' => $t->int(),
				'city' => $t->string(),
				'country' => $t->string()
			]),
			'projects' => $t->list(
				$t->arrayShape([
					'name' => $t->string(),
					'budget' => $t->float(),
					'completed' => $t->bool()
				])
			)
		]);
	}

	/**
	 * @return mixed[]
	 */
	protected function getValidValuesForLargeSchema(): array
	{
		return [
			'id' => 123,
			'name' => 'John Doe',
			'email' => 'john.doe@example.com',
			'age' => 30,
			'active' => true,
			'score' => 95.5,
			'tags' => ['php', 'developer', 'senior'],
			'address' => [
				'street' => 'Main Street',
				'number' => 123,
				'city' => 'Prague',
				'country' => 'Czech Republic'
			],
			'projects' => [
				[
					'name' => 'Project A',
					'budget' => 10000.0,
					'completed' => true
				],
				[
					'name' => 'Project B',
					'budget' => 15000.5,
					'completed' => false
				]
			]
		];
	}

}
