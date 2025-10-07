<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class ErrorsTest extends TestCase
{

	/** @var array<string, int<1, max>> */
	private array $called = [];

	protected function setUp(): void
	{
		$this->called = [];
	}

	public function testCorrectSchema(): void
	{
		$values = [
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

		$this->assertTrue($this->getProcessor()->matches($values, $this->createLargeSchema()));
		$this->assertSame($values, $this->getProcessor()->process($values, $this->createLargeSchema()));
	}

	public function testCollectErrors(): void
	{
		$exception = $this->getCollectionForLargeSchema([
			'id' => 123,
			'name' => 15,
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
					'completed' => null
				],
				[
					'name' => 'Project B',
					'completed' => false
				]
			]
		]);

		$this->assertSame(<<<'ERROR'
Invalid type int, expected string.
  → at name
Invalid type null, expected bool.
  → at projects.[0].completed
Key is missing.
  → at projects.[1].budget
ERROR, $exception->toPrettyString(''));
		$this->assertSame([
			'id' => 1,
			'email' => 1,
			'age' => 1,
			'active' => 1,
			'score' => 1,
			'tags' => 1,
			'address.street' => 1,
			'address.number' => 1,
			'address.city' => 1,
			'address.country' => 1,
			'address' => 1,
			'projects.name' => 2,
			'projects.budget' => 1,
			'projects.completed' => 1,
		], $this->called);
	}

	public function testFirstError(): void
	{
		$exception = $this->getSingleErrorForLargeSchema([
			'id' => 123,
			'name' => 15,
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
					'completed' => null
				],
				[
					'name' => 'Project B',
					'completed' => false
				]
			]
		]);

		$this->assertSame(<<<'ERROR'
Invalid type int, expected string.
  → at name
ERROR, $exception->toPrettyString(''));
		$this->assertSame([
			'id' => 1,
		], $this->called);
	}

	private function getCollectionForLargeSchema(mixed $value): AssertException
	{
		try {
			$this->getProcessor()->process($value, $this->createLargeSchema());
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $e) {
			return $e;
		}
	}

	private function getSingleErrorForLargeSchema(mixed $value): AssertException
	{
		try {
			$this->getProcessor()->processFast($value, $this->createLargeSchema());
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $e) {
			return $e;
		}
	}

	private function createLargeSchema(): Type
	{
		$t = TypeSchema::get();
		$after = function (string $name): mixed {
			return function (mixed $v) use ($name): mixed {
				if (!isset($this->called[$name])) {
					$this->called[$name] = 1;
				} else {
					$this->called[$name]++;
				}

				return $v;
			};
		};
		return $t->arrayShape([
			'id' => $t->int()->after($after('id')),
			'name' => $t->string()->after($after('name')),
			'email' => $t->string()->after($after('email')),
			'age' => $t->int()->after($after('age')),
			'active' => $t->bool()->after($after('active')),
			'score' => $t->float()->after($after('score')),
			'tags' => $t->list($t->string())->after($after('tags')),
			'address' => $t->arrayShape([
				'street' => $t->string()->after($after('address.street')),
				'number' => $t->int()->after($after('address.number')),
				'city' => $t->string()->after($after('address.city')),
				'country' => $t->string()->after($after('address.country')),
			])->after($after('address')),
			'projects' => $t->list(
				$t->arrayShape([
					'name' => $t->string()->after($after('projects.name')),
					'budget' => $t->float()->after($after('projects.budget')),
					'completed' => $t->bool()->after($after('projects.completed')),
				])
			)->after($after('projects')),
		]);
	}

}
