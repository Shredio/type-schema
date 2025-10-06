<?php declare(strict_types = 1);

namespace Tests\Unit\Config;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Tests\TestCase;

final class TypeHierarchyConfigTest extends TestCase
{

	public function testXx(): void
	{
		$schema = $this->createLargeTypeSchema();
		$values = [
			'id' => 123,
			'name' => 'John Doe',
			'email' => 'john.doe@example.com',
			'age' => '30',
			'active' => 'true',
			'score' => '95.5',
			'tags' => ['php', 'developer', 'senior'],
			'address' => [
				'street' => 'Main Street',
				'number' => '123',
				'city' => 'Prague',
				'country' => 'Czech Republic'
			],
			'projects' => [
				[
					'name' => 'Project A',
					'budget' => '10000.0',
					'completed' => true
				],
				[
					'name' => 'Project B',
					'budget' => '15000.5',
					'completed' => false
				]
			]
		];

		$config = new TypeConfig();
		$inputConfig = $config->withConversionStrategy(ConversionStrategyFactory::lenient());
		$this->assertTrue($this->getProcessor()->matches($values, $schema, $config->withHierarchy([
			'age' => $inputConfig,
			'active' => $inputConfig,
			'score' => $inputConfig,
			'address' => [
				'number' => $inputConfig,
			],
			'projects' => [
				'budget' => $inputConfig,
			],
		])));
	}

}
