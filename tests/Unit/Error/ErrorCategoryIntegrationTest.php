<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Enum\ExtraKeysBehavior;
use Shredio\TypeSchema\Error\ErrorCategory;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorReport;
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;

final class ErrorCategoryIntegrationTest extends TestCase
{

	public function testWrongFieldTypeIsStructural(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'age' => TypeSchema::get()->int(),
		]);

		$reports = $this->getReports($processor->parse(['age' => 'not-a-number'], $schema, collectErrors: true));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Structural, $reports[0]->category);
		$this->assertSame(['age'], $reports[0]->toArrayPath());
	}

	public function testMissingFieldIsStructural(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'name' => TypeSchema::get()->string(),
		]);

		$reports = $this->getReports($processor->parse([], $schema, collectErrors: true));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Structural, $reports[0]->category);
	}

	public function testExtraFieldIsStructural(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape(
			['name' => TypeSchema::get()->string()],
			extraItems: ExtraKeysBehavior::Reject,
		);

		$reports = $this->getReports($processor->parse(
			['name' => 'Alice', 'unexpected' => 'value'],
			$schema,
			collectErrors: true,
		));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Structural, $reports[0]->category);
	}

	public function testRangeConstraintIsValidation(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
		]);

		$reports = $this->getReports($processor->parse(['age' => 5], $schema, collectErrors: true));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Validation, $reports[0]->category);
		$this->assertSame(['age'], $reports[0]->toArrayPath());
	}

	public function testCustomValidateIsValidation(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'name' => TypeSchema::get()->string()->validate(
				fn (string $value, $context): ?ErrorElement => $value === ''
					? $context->errorElementFactory->createError('Name cannot be empty.')
					: null,
			),
		]);

		$reports = $this->getReports($processor->parse(['name' => ''], $schema, collectErrors: true));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Validation, $reports[0]->category);
	}

	public function testMixedStructuralAndValidationErrorsInOnePayload(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'name' => TypeSchema::get()->string(),
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
		]);

		$reports = $this->getReports($processor->parse(
			['name' => 42, 'age' => 5],
			$schema,
			collectErrors: true,
		));

		$byPath = [];
		foreach ($reports as $report) {
			$byPath[implode('.', array_map('strval', $report->toArrayPath()))] = $report->category;
		}

		$this->assertSame(ErrorCategory::Structural, $byPath['name'] ?? null);
		$this->assertSame(ErrorCategory::Validation, $byPath['age'] ?? null);
	}

	public function testResolveReturnsStructuralWhenAnyReportIsStructural(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'name' => TypeSchema::get()->string(),
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
		]);

		$reports = $this->getReports($processor->parse(
			['name' => 42, 'age' => 5],
			$schema,
			collectErrors: true,
		));

		$this->assertSame(ErrorCategory::Structural, ErrorCategory::resolve($reports));
		$this->assertTrue(ErrorCategory::hasStructural($reports));
	}

	public function testResolveReturnsValidationWhenAllReportsAreValidation(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
			'score' => TypeSchema::get()->intRange(min: 0, max: 100),
		]);

		$reports = $this->getReports($processor->parse(
			['age' => 5, 'score' => 500],
			$schema,
			collectErrors: true,
		));

		$this->assertSame(ErrorCategory::Validation, ErrorCategory::resolve($reports));
		$this->assertFalse(ErrorCategory::hasStructural($reports));
	}

	public function testResolveReturnsValidationForEmptyReports(): void
	{
		$this->assertSame(ErrorCategory::Validation, ErrorCategory::resolve([]));
		$this->assertFalse(ErrorCategory::hasStructural([]));
	}

	public function testAssertExceptionCategoryIsStructuralWhenAnyErrorIsStructural(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'name' => TypeSchema::get()->string(),
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
		]);

		try {
			$processor->process(['name' => 42, 'age' => 5], $schema);
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $exception) {
			$this->assertSame(ErrorCategory::Structural, $exception->getCategory());
		}
	}

	public function testAssertExceptionCategoryIsValidationWhenAllErrorsAreValidation(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'age' => TypeSchema::get()->intRange(min: 18, max: 120),
		]);

		try {
			$processor->process(['age' => 5], $schema);
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $exception) {
			$this->assertSame(ErrorCategory::Validation, $exception->getCategory());
		}
	}

	public function testNestedStructuralErrorKeepsCategory(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->arrayShape([
			'user' => TypeSchema::get()->arrayShape([
				'email' => TypeSchema::get()->string(),
			]),
		]);

		$reports = $this->getReports($processor->parse(
			['user' => ['email' => 123]],
			$schema,
			collectErrors: true,
		));

		$this->assertCount(1, $reports);
		$this->assertSame(ErrorCategory::Structural, $reports[0]->category);
		$this->assertSame(['user', 'email'], $reports[0]->toArrayPath());
	}

	public function testListItemValidationConstraint(): void
	{
		$processor = TypeSchemaProcessor::createDefault();
		$schema = TypeSchema::get()->list(
			TypeSchema::get()->intRange(min: 0, max: 10),
		);

		$reports = $this->getReports($processor->parse([5, 20, -1], $schema, collectErrors: true));

		$this->assertCount(2, $reports);
		foreach ($reports as $report) {
			$this->assertSame(ErrorCategory::Validation, $report->category);
		}
	}

	/**
	 * @return list<ErrorReport>
	 */
	private function getReports(mixed $result): array
	{
		$this->assertInstanceOf(ErrorElement::class, $result);
		return $result->getReports();
	}

}
