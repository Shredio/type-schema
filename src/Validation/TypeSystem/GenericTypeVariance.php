<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation\TypeSystem;

enum GenericTypeVariance
{

	case Invariant;
	case Covariant;
	case Contravariant;

}
