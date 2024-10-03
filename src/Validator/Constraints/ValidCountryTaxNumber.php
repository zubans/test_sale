<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[Attribute]
class ValidCountryTaxNumber extends Constraint
{
    public string $message = 'This is not a valid country tax number.';
}