<?php declare(strict_types = 1);

namespace Tests\Unit\Symfony\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;

final class UserFixture
{

	public function __construct(
		#[Assert\NotBlank]
		#[Assert\Length(min: 2, max: 100)]
		public string $name = '',
		#[Assert\Positive]
		public int $age = 0,
	)
	{
	}

}
