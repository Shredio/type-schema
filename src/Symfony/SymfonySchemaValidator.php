<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class SymfonySchemaValidator
{

	public function __construct(
		public ValidatorInterface $validator,
	)
	{
	}

}
