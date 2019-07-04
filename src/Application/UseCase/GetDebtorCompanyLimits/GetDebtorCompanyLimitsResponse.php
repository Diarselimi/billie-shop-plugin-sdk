<?php

namespace App\Application\UseCase\GetDebtorCompanyLimits;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtorResponse\BaseMerchantDebtorContainer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="GetDebtorCompanyLimitsResponse",
 *      title="Get Debtor Company Limits Response",
 *      type="object",
 *      properties={
 *          @OA\Property(property="company_id", type="integer"),
 *          @OA\Property(property="company_uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="financing_power", type="number", format="float", description="Current available limit for this company in Platform."),
 *          @OA\Property(property="merchant_debtors", type="array", description="List of debtors created in Paella for this company.",
 *              @OA\Items(
 *                  type="object",
 *                  properties={
 *                      @OA\Property(property="merchant_id", type="integer"),
 *                      @OA\Property(property="merchant_debtor_id", type="integer"),
 *                      @OA\Property(property="merchant_debtor_uuid", ref="#/components/schemas/UUID"),
 *                      @OA\Property(property="financing_limit", type="number", format="float", description="Full limit assigned in Paella to this debtor and merchant combination."),
 *                      @OA\Property(property="financing_power", type="number", format="float", description="Current available limit in Paella for this debtor and merchant combination."),
 *                  }
 *              )
 *          ),
 *      }
 * )
 */
class GetDebtorCompanyLimitsResponse implements ArrayableInterface
{
    private $company;

    private $merchantDebtors;

    public function __construct(DebtorCompany $company, BaseMerchantDebtorContainer ...$merchantDebtors)
    {
        $this->company = $company;
        $this->merchantDebtors = $merchantDebtors;
    }

    public function getCompany(): DebtorCompany
    {
        return $this->company;
    }

    /**
     * @return BaseMerchantDebtorContainer[]
     */
    public function getMerchantDebtors(): array
    {
        return $this->merchantDebtors;
    }

    public function toArray(): array
    {
        $merchantDebtors = [];

        foreach ($this->getMerchantDebtors() as $container) {
            $merchantDebtors[] = [
                'merchant_id' => $container->getMerchantDebtor()->getMerchantId(),
                'merchant_debtor_id' => $container->getMerchantDebtor()->getId(),
                'merchant_debtor_uuid' => $container->getMerchantDebtor()->getUuid(),
                'financing_limit' => $container->getFinancialDetails()->getFinancingLimit(),
                'financing_power' => $container->getFinancialDetails()->getFinancingPower(),
            ];
        }

        return [
            'company_id' => $this->getCompany()->getId(),
            'company_uuid' => $this->getCompany()->getUuid(),
            'company_financing_power' => $this->getCompany()->getFinancingPower(),
            'merchant_debtors' => $merchantDebtors,
        ];
    }
}
