<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingStepNotFoundException extends \Exception
{
    protected $message = 'Merchant onboarding step could not be found.';
}
