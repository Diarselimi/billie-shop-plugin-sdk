<?php

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

class FinancialAssessmentNotFoundException extends \RuntimeException
{
    protected $message = 'No financial assessment found.';
}
