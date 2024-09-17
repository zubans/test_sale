<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidCountryTaxNumberValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        $countryCode = substr($value, 0, 2);

        if (!in_array($countryCode, $this->context->getObject()->countries)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}