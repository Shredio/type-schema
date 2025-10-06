<?php declare(strict_types = 1);

namespace Tests\Common;

use Generator;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

trait TestCaseTrait
{

	/** @var resource */
	private mixed $_resource;

	#[Before]
	protected function setUpResource(): void
	{
		$resource = fopen('php://memory', 'r');
		if ($resource === false) {
			throw new \RuntimeException('Failed to open memory stream.');
		}

		$this->_resource = $resource;
	}

	#[After]
	protected function tearDownResource(): void
	{
		fclose($this->_resource);
	}

	/**
	 * @return resource
	 */
	protected function resource(): mixed
	{
		return $this->_resource;
	}

	/**
	 * @param array<mixed> $values
	 * @return Generator<int, mixed, void, void>
	 */
	protected function generator(array $values): Generator
	{
		foreach ($values as $value) {
			yield $value;
		}
	}

	protected function emoji(): string
	{
		return '🚀';
	}

	protected function multiByteString(): string
	{
		return 'こんにちは';
	}

	protected function stringable(string $value): object
	{
		return new readonly class($value) {
			public function __construct(private string $value) {}
			public function __toString(): string { return $this->value; }
		};
	}

	public function callableObject(mixed $return = null): object
	{
		return new readonly class($return) {
			public function __construct(private mixed $return) {}
			public function __invoke(): mixed { return $this->return; }
		};
	}

}
