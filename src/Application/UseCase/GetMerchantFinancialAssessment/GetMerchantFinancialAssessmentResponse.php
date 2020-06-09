<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantFinancialAssessmentResponse", title="Financial Assessment Response",
 *     properties={
 *      @OA\Property(property="yearly_transaction_volume", type="number", format="float", example=22.33),
 *      @OA\Property(property="mean_invoice_amount", type="number", format="float", example=233.44),
 *      @OA\Property(property="cancellation_rate", type="number", format="float", example=23.3),
 *      @OA\Property(property="invoice_duration", type="integer", example=100),
 *      @OA\Property(property="returning_order_rate", type="number", format="float", example=90.0, nullable=true),
 *      @OA\Property(property="default_rate", type="number", format="float", example=50.0, nullable=true),
 *      @OA\Property(property="high_invoice_amount", type="number", format="float", example=50.55, nullable=true),
 *      @OA\Property(property="digital_goods_rate", type="number", format="float", example=50.0, nullable=true),
 *     })
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
        $dataArray = $this->financialAssessmentEntity->getData();

        return [
            'yearly_transaction_volume' => $dataArray['yearly_transaction_volume'],
            'mean_invoice_amount' => $dataArray['mean_invoice_amount'],
            'cancellation_rate' => $dataArray['cancellation_rate'],
            'invoice_duration' => $dataArray['invoice_duration'],
            'returning_order_rate' => $dataArray['returning_order_rate'],
            'default_rate' => $dataArray['default_rate'],
            'high_invoice_amount' => $dataArray['high_invoice_amount'] ?? null,
            'digital_goods_rate' => $dataArray['digital_goods_rate'] ?? null,
        ];
    }
}
