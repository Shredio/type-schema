<?php declare(strict_types = 1);

namespace Tests\Unit\PhpStan\Classes;

enum TestStringEnum: string
{
	case First = 'first';
	case Second = 'second';
}
