<?php

namespace App\Application\Exception;

class MerchantOnboardingStepTransitionException extends \RuntimeException
{
    protected $message = 'Merchant Onboarding Step transition is not possible.';
}
