<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Renderer;

use DateTimeImmutable;
use DateTimeZone;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;
use stdClass;

final class DeveloperValidationMessageFactoryTest extends TestCase
{

	/**
	 * @return iterable<string, array{mixed, string}>
	 */
	public static function provideValues(): iterable
	{
		yield 'null' => [null, 'null'];
		yield 'true' => [true, 'true'];
		yield 'false' => [false, 'false'];
		yield 'int' => [42, '42'];
		yield 'float' => [1.5, '1.5'];
		yield 'float with zero fraction' => [2.0, '2.0'];
		yield 'nan' => [NAN, 'NAN'];
		yield 'infinity' => [INF, 'INF'];
		yield 'negative infinity' => [-INF, '-INF'];
		yield 'string' => ['hello', '"hello"'];
		yield 'string with slash' => ['a/b', '"a/b"'];
		yield 'empty string' => ['', '""'];
		yield 'string at length limit' => [str_repeat('a', 40), sprintf('"%s"', str_repeat('a', 40))];
		yield 'long string is truncated' => [sprintf('%s!', str_repeat('abcd', 10)), sprintf('"%s" (truncated)', str_repeat('abcd', 10))];
		yield 'multibyte string is measured in characters' => [str_repeat('č', 40), json_encode(str_repeat('č', 40))];
		yield 'long multibyte string is truncated by characters' => [sprintf('%s!', str_repeat('čřžý', 10)), sprintf('%s (truncated)', json_encode(str_repeat('čřžý', 10)))];
		yield 'string with control character' => ["a\x00b", 'string'];
		yield 'invalid utf-8' => ["\xFF", 'string'];
		yield 'midnight date' => [new DateTimeImmutable('2024-01-15 00:00:00', new DateTimeZone('Europe/Prague')), '2024-01-15 (Europe/Prague)'];
		yield 'date with time' => [new DateTimeImmutable('2024-01-15 10:20:30', new DateTimeZone('UTC')), '2024-01-15T10:20:30+00:00'];
		yield 'object' => [new stdClass(), 'stdClass'];
		yield 'array' => [[1, 2], 'array'];
	}

	#[DataProvider('provideValues')]
	public function testDescribeValue(mixed $value, string $expected): void
	{
		$this->assertSame($expected, DeveloperValidationMessageFactory::describeValue($value));
	}

	public function testInvalidTypeShowsValueOnlyForScalars(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('int'));

		$this->assertSame('Invalid type string with value "x", expected int.', DeveloperValidationMessageFactory::invalidType($definition, 'x'));
		$this->assertSame('Invalid type array, expected int.', DeveloperValidationMessageFactory::invalidType($definition, []));
		$this->assertSame('Invalid type null, expected int.', DeveloperValidationMessageFactory::invalidType($definition, null));
	}

	public function testKeyMessagesNameTheLastPathSegment(): void
	{
		$this->assertSame('Key is missing.', DeveloperValidationMessageFactory::missingField());
		$this->assertSame('Key \'name\' is missing.', DeveloperValidationMessageFactory::missingField(['user', 'name']));
		$this->assertSame('Extra key found.', DeveloperValidationMessageFactory::extraField());
		$this->assertSame('Extra key \'3\' found.', DeveloperValidationMessageFactory::extraField(['items', 3]));
	}

	public function testSimpleMessages(): void
	{
		$this->assertSame('Value should not be empty, "" given.', DeveloperValidationMessageFactory::notEmpty(''));
		$this->assertSame('Value 1 is not equal to 2.', DeveloperValidationMessageFactory::equalTo(1, '2'));
		$this->assertSame('Invalid date format, "x" given.', DeveloperValidationMessageFactory::invalidDate('x'));
		$this->assertSame(
			'Item count 5 is not in the expected range [min, 3].',
			DeveloperValidationMessageFactory::itemCountRange(5, NumberInclusiveRange::fromInts(max: 3), RangeInclusiveDecision::ShouldBeLessOrEqual),
		);
		$this->assertSame(
			'Value 0 is not in the expected range [1, 10].',
			DeveloperValidationMessageFactory::numberRangeDecision(0, NumberInclusiveRange::fromInts(1, 10), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
		);
	}

	public function testValueNotInAllowedValues(): void
	{
		$this->assertSame(
			'Value "aple" is not a valid choice, did you mean "apple"? Possible values are "apple", "pear" or "plum".',
			DeveloperValidationMessageFactory::valueNotInAllowedValues('aple', ['apple', 'pear', 'plum']),
		);
		$this->assertSame(
			'Value "x" is not a valid choice. Possible value is "apple".',
			DeveloperValidationMessageFactory::valueNotInAllowedValues('x', ['apple', 1]),
		);
		$this->assertSame('Value 3 is not a valid choice.', DeveloperValidationMessageFactory::valueNotInAllowedValues(3, [1, 2]));
	}

	public function testDidYouMean(): void
	{
		$this->assertSame(', did you mean "apple"?', DeveloperValidationMessageFactory::didYouMean(['apple', 'pear'], 'appel'));
		$this->assertSame('.', DeveloperValidationMessageFactory::didYouMean(['apple', 'pear'], 'something else'));
		$this->assertSame('.', DeveloperValidationMessageFactory::didYouMean(['apple'], 1));
		$this->assertSame('.', DeveloperValidationMessageFactory::didYouMean([], 'apple'));
	}

	public function testGetSuggestion(): void
	{
		$this->assertSame('apple', DeveloperValidationMessageFactory::getSuggestion(['pear', 'apple'], 'appel'));
		$this->assertSame('pear', DeveloperValidationMessageFactory::getSuggestion(['pear', 'peach'], 'pea'));
		$this->assertNull(DeveloperValidationMessageFactory::getSuggestion(['apple'], 'zzzzzz'));
		$this->assertNull(DeveloperValidationMessageFactory::getSuggestion(['apple'], ''));
		$this->assertNull(DeveloperValidationMessageFactory::getSuggestion([], 'apple'));
	}

	public function testPossibleValues(): void
	{
		$this->assertSame('', DeveloperValidationMessageFactory::possibleValues([], 100));
		$this->assertSame('Possible value is "a".', DeveloperValidationMessageFactory::possibleValues(['a'], 100));
		$this->assertSame(' Possible values are "a" or "b".', DeveloperValidationMessageFactory::possibleValues(['a', 'b'], 100, ' '));
		$this->assertSame('Possible values are "a", "b" or "c".', DeveloperValidationMessageFactory::possibleValues(['a', 'b', 'c'], 36));
		$this->assertSame('', DeveloperValidationMessageFactory::possibleValues(['a', 'b', 'c'], 35), 'Too long list is omitted.');
	}

}
