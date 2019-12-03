<?php

namespace App\Application\UseCase\MerchantOnboardingTransition;

class MerchantOnboardingStepsIncompleteException extends \Exception
{
    protected $message = 'There are merchant onboarding steps that are not yet complete.';
}
