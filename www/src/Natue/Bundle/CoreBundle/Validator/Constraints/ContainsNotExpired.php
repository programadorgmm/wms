<?php

namespace Natue\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsNotExpired extends Constraint
{
    public $message = 'The "%string%" is past due.';
}
