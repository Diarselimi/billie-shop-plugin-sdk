<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InvitedUserTcAccepted extends Constraint
{
    public $message = 'Terms & Conditions should be accepted';
}
