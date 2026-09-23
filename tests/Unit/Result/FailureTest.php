<?php declare(strict_types = 1);

namespace Tests\Unit\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Result\Failure;
use Symfony\Component\Translation\IdentityTranslator;

#[CoversClass(Failure::class)]
final class FailureTest extends TestCase
{

	public function testCategoryIsStructuralWhenAnyErrorIsStructural(): void
	{
		$failure = new Failure(new IssueCollection([
			new CustomIssue('validation'),
			new MissingKey(),
		]));

		$this->assertSame(ErrorCategory::Structural, $failure->getCategory());
	}

	public function testCategoryIsValidationWhenAllErrorsAreValidation(): void
	{
		$failure = new Failure(new CustomIssue('validation'), new ExtraKey());

		$this->assertSame(ErrorCategory::Validation, $failure->getCategory());
	}

	public function testGetReportsUsesRenderer(): void
	{
		$failure = new Failure(new IssuePath(new MissingKey(), new Path('name')));

		$english = $failure->getReports();
		$symfony = $failure->getReports(new SymfonyIssueRenderer(new IdentityTranslator()));

		$this->assertSame('Please provide a value for this field.', (string) $english[0]->message);
		$this->assertSame('This field is missing.', (string) $symfony[0]->message);
		$this->assertSame(['name'], $symfony[0]->toArrayPath());
	}

	public function testGetNoticeReports(): void
	{
		$this->assertSame([], (new Failure(new MissingKey()))->getNoticeReports());

		$failure = new Failure(new MissingKey(), new IssuePath(new ExtraKey(), new Path('extra')));
		$notices = $failure->getNoticeReports();

		$this->assertCount(1, $notices);
		$this->assertSame(['extra'], $notices[0]->toArrayPath());
		$this->assertSame('This field is not allowed.', (string) $notices[0]->message);
	}

	public function testWithNoticesAsErrorsWithoutNoticesReturnsSameInstance(): void
	{
		$failure = new Failure(new MissingKey());

		$this->assertSame($failure, $failure->withNoticesAsErrors());
	}

	public function testWithNoticesAsErrorsMergesNotices(): void
	{
		$missingKey = new MissingKey();
		$extraKey = new ExtraKey();
		$failure = new Failure($missingKey, $extraKey);

		$promoted = $failure->withNoticesAsErrors();

		$this->assertNull($promoted->notices);
		$issues = $promoted->errors->getIssues();
		$this->assertCount(2, $issues);
		$this->assertSame($missingKey, $issues[0]->issue);
		$this->assertSame($extraKey, $issues[1]->issue);
	}

}
