<?php

namespace spec\App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use PhpSpec\ObjectBehavior;

class MerchantDebtorLimitsServiceSpec extends ObjectBehavior
{
    public function let(
        CompaniesServiceInterface $companyService,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantDebtorLimitsService::class);
    }
}
