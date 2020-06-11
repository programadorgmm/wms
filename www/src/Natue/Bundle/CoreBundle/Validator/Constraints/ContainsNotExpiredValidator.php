<?php

namespace Natue\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsNotExpiredValidator extends ConstraintValidator
{
    /**
     * @param \DateTime  $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        $today = new \DateTime('now');

        if ($value <= $today) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%string%', $value->format('d/m/Y'))
                ->addViolation();
        }
    }
}
