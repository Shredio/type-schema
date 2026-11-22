<?php declare(strict_types = 1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class RuntimeDependenciesTest extends TestCase
{

	/**
	 * nette/utils is only a dev dependency, the library must work without it.
	 */
	public function testSourceDoesNotUseNette(): void
	{
		$filesUsingNette = [];
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src', RecursiveDirectoryIterator::SKIP_DOTS));

		/** @var SplFileInfo $file */
		foreach ($files as $file) {
			$code = file_get_contents($file->getPathname());
			if ($code !== false && preg_match('#\bNette\\\\#', $code) === 1) {
				$filesUsingNette[] = $file->getFilename();
			}
		}

		$this->assertSame([], $filesUsingNette);
	}

}
