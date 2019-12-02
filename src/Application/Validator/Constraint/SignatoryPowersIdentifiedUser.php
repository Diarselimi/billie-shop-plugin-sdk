<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SignatoryPowersIdentifiedUser extends Constraint
{
    public $message = 'There can be one or no users selected as current user.';
}
