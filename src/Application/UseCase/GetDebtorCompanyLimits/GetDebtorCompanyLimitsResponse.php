<?php

namespace App\Application\UseCase\GetDebtorCompanyLimits;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorLimit\DebtorLimitDTO;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="GetDebtorCompanyLimitsMerchant",
 *      title="Debtor's Merchant IDs Object",
 *      type="object",
 *      properties={
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="payment_uuid", ref="#/components/schemas/UUID"),
 *      }
 * )
 *
 * @OA\Schema(
 *      schema="GetDebtorCompanyLimitsResponse",
 *      title="Debtor Company Limits Object",
 *      type="object",
 *      properties={
 *          @OA\Property(property="company_id", type="integer"),
 *          @OA\Property(property="company_uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="company_financing_power", allOf={@OA\Schema(ref="#/components/schemas/Money")}, example=10000.00, description="Current available limit for this company in Platform."),
 *          @OA\Property(property="merchant_debtors", type="array", description="List of debtors created in Paella for this company.",
 *              @OA\Items(
 *                  type="object",
 *                  properties={
 *                      @OA\Property(property="id", type="integer", description="Merchant Debtor ID"),
 *                      @OA\Property(property="uuid", ref="#/components/schemas/UUID", description="Merchant Debtor UUID"),
 *                      @OA\Property(property="financing_limit", allOf={@OA\Schema(ref="#/components/schemas/Money")}, nullable=true, example=2500.00, description="Full limit assigned in Paella to this debtor and merchant combination."),
 *                      @OA\Property(property="financing_power", allOf={@OA\Schema(ref="#/components/schemas/Money")}, nullable=true, example=2251.01, description="Current available limit in Paella for this debtor and merchant combination."),
 *                      @OA\Property(property="merchant", ref="#/components/schemas/GetDebtorCompanyLimitsMerchant", description="The merchant the merchant-debtor belongs to."),
 *                  }
 *              )
 *          ),
 *      }
 * )
 */
class GetDebtorCompanyLimitsResponse implements ArrayableInterface
{
    private $company;

    private $limit;

    private $merchantDebtors;

    public function __construct(
        DebtorCompany $company,
        ?DebtorLimitDTO $limit = null,
        MerchantDebtorContainer ...$merchantDebtors
    ) {
        $this->company = $company;
        $this->limit = $limit;
        $this->merchantDebtors = $merchantDebtors;
    }

    public function getCompany(): DebtorCompany
    {
        return $this->company;
    }

    /**
     * @return MerchantDebtorContainer[]
     */
    public function getMerchantDebtors(): array
    {
        return $this->merchantDebtors;
    }

    public function getLimit(): ? DebtorLimitDTO
    {
        return $this->limit;
    }

    public function toArray(): array
    {
        $merchantDebtors = [];

        foreach ($this->getMerchantDebtors() as $container) {
            $debtorCustomerLimit = $container->getDebtorCustomerLimit();

            $merchantDebtors[] = [
                'id' => $container->getMerchantDebtor()->getId(),
                'uuid' => $container->getMerchantDebtor()->getUuid(),
                'financing_limit' => $debtorCustomerLimit ? $debtorCustomerLimit->getFinancingLimit() : null,
                'financing_power' => $debtorCustomerLimit ? $debtorCustomerLimit->getAvailableFinancingLimit() : null,
                'merchant' => [
                    'id' => $container->getMerchant()->getId(),
                    'payment_uuid' => $container->getMerchant()->getPaymentUuid(),
                ],
            ];
        }

        return [
            'company_id' => $this->getCompany()->getId(),
            'company_uuid' => $this->getCompany()->getUuid(),
            'company_financing_power' => $this->getLimit() ? $this->getLimit()->getGlobalAvailableFinancingLimit() : null,
            'merchant_debtors' => $merchantDebtors,
        ];
    }
}
