<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantFinancialAssessmentResponse", title="Financial Assessment Response",
 *     properties={
 *      @OA\Property(property="yearly_transaction_volume",type="number", format="float", example=22.33),
 *      @OA\Property(property="mean_invoice_number",type="number", format="float", example=233.44),
 *      @OA\Property(property="cancellation_rate",type="number", format="float", example=23.3),
 *      @OA\Property(property="invoice_duration",type="integer", example=100),
 *      @OA\Property(property="returning_order_rate",type="number", format="float", example=90.0),
 *      @OA\Property(property="default_rate",type="number", format="float", example=50.0)
 *     })
 *    })
 */
class GetMerchantFinancialAssessmentResponse implements ArrayableInterface
{
    private $financialAssessmentEntity;

    public function __construct(MerchantFinancialAssessmentEntity $financialAssessmentEntity)
    {
        $this->financialAssessmentEntity = $financialAssessmentEntity;
    }

    public function toArray(): array
    {
        return $this->financialAssessmentEntity->getData();
    }
}
