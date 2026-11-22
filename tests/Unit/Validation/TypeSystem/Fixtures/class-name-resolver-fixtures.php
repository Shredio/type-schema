<?php declare(strict_types = 1);

namespace Tests\Unit\Validation\TypeSystem\Fixtures\First {

	use DateTimeImmutable;
	use Shredio\TypeSchema\Types as SchemaTypes;
	use Shredio\TypeSchema\Issue\{IssueNode, Issue as BaseIssue};
	use /* comment */ Shredio\TypeSchema\Result\Success, Shredio\TypeSchema\Result\Failure as ParseFailure;
	use \Shredio\TypeSchema\Result\WithNotices;
	use function sprintf;
	use const PHP_EOL;

	class ParentClass
	{

	}

	final class ChildClass extends ParentClass
	{

		public function method(): string
		{
			$value = 1;
			$closure = static function () use ($value): string {
				return "{$value}";
			};

			return sprintf('%s%s%s', $closure(), DateTimeImmutable::class, PHP_EOL);
		}

		use SomeTrait;

	}

	use Shredio\TypeSchema\TypeSchema as LateImport;

	trait SomeTrait
	{

	}

	/**
	 * @template T
	 */
	interface Box
	{

	}

	/**
	 * @template TItem
	 * @implements Box<TItem>
	 */
	final class ListBox implements Box
	{

	}

	/**
	 * @template TKey of array-key
	 * @template TValue of SchemaTypes\Type
	 * @implements Box<TValue>
	 */
	final class MapBox implements Box
	{

	}

}

namespace Tests\Unit\Validation\TypeSystem\Fixtures\Second {

	use Shredio\TypeSchema\Issue\InvalidType as ParentClass;

	interface SecondInterface
	{

	}

	enum SecondEnum
	{

		case One;

	}

}

namespace {

	use Shredio\TypeSchema\TypeSchema;

	final class ClassNameResolverGlobalFixture
	{

		public function get(): TypeSchema
		{
			return TypeSchema::get();
		}

	}

}
