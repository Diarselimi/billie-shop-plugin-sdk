<?php

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

class MerchantFinancialAssessmentAlreadyExistsException extends \RuntimeException
{
    protected $message = 'Merchant Financial Assessment already exists.';
}
