<?php declare(strict_types = 1);

namespace Tests;

use LogicException;
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Types\Type;
use Tests\Common\TestCaseTrait;
use Tests\Common\TestConversionStrategy;
use Tests\Common\TypeTestCrate;

abstract class TypeTestCase extends TestCase
{

	use TestCaseTrait;

	/**
	 * @return iterable<string|int, mixed>
	 */
	abstract protected function getValidValues(): iterable;

	/**
	 * @return iterable<string|int, mixed>
	 */
	abstract protected function getInvalidValues(): iterable;

	final public function testValidCases(): void
	{
		$type = null;
		$calledMethodsInTypeConverter = [];
		$processor = $this->getProcessor();

		foreach ($this->getValidValues() as $givenValueDescription => $value) {
			if ($value instanceof TypeTestCrate) {
				$type = $value->type;
				$calledMethodsInTypeConverter = $value->calledMethodsInTypeConverter;
				continue;
			}

			if (!is_string($givenValueDescription)) {
				$givenValueDescription = get_debug_type($value);
			}

			if ($type === null) {
				throw new LogicException('Type is not set. Make sure to call typeToTest() as the first yielded value from getValidValues().');
			}

			$conversionStrategy = new TestConversionStrategy();
			$matches = $processor->matches($value, $type, new TypeConfig($conversionStrategy));
			$this->assertTrue(
				$matches,
				'Failed asserting that \'' . $givenValueDescription . '\' case is valid value for ' . $this->getClassName($type::class),
			);

			$unique = array_unique($conversionStrategy->called);
			$this->assertEquals(
				$calledMethodsInTypeConverter,
				$unique,
				$this->getMessageForTypeConverter($calledMethodsInTypeConverter, $unique, $givenValueDescription, $type::class),
			);
		}
	}

	public function testInvalidCases(): void
	{
		if (!$this->hasInvalidTypes()) {
			$this->assertTrue(true); // Just to mark the test as passed
			return;
		}

		$type = null;
		$processor = $this->getProcessor();

		foreach ($this->getInvalidValues() as $givenValueDescription => $value) {
			if ($value instanceof TypeTestCrate) {
				$type = $value->type;
				continue;
			}

			if (!is_string($givenValueDescription)) {
				$givenValueDescription = get_debug_type($value);
			}

			if ($type === null) {
				throw new LogicException('Type is not set. Make sure to call typeToTest() as the first yielded value from getInvalidValues().');
			}

			$conversionStrategy = new TestConversionStrategy();
			$matches = $processor->matches($value, $type, new TypeConfig($conversionStrategy));
			$this->assertFalse(
				$matches,
				'Failed asserting that ' . $givenValueDescription . ' is invalid value for ' . $this->getClassName($type::class),
			);
		}
	}

	/**
	 * @param Type<mixed> $type
	 * @param list<'string'|'int'|'float'|'bool'|'null'|'array'|'object'|'backedEnum'|'unitEnum'> $calledMethodsInTypeConverter
	 */
	protected function typeToTest(
		Type $type,
		array $calledMethodsInTypeConverter = [],
	): TypeTestCrate
	{
		return new TypeTestCrate($type, $calledMethodsInTypeConverter);
	}

	protected function hasInvalidTypes(): bool
	{
		return true;
	}

	/**
	 * @param string[] $expected
	 * @param string[] $given
	 */
	private function getMessageForTypeConverter(array $expected, array $given, string $givenValueDescription, string $type): string
	{
		return sprintf(
			'Failed asserting that the called methods in TypeConverter matched for \'%s\' case of type %s. Expected: [%s], Given: [%s]',
			$givenValueDescription,
			$this->getClassName($type),
			implode(', ', $expected),
			implode(', ', $given),
		);
	}

	private function getClassName(string $fullName): string
	{
		$pos = strrpos($fullName, '\\');
		if ($pos === false) {
			return $fullName;
		}

		return substr($fullName, $pos + 1);
	}

}
