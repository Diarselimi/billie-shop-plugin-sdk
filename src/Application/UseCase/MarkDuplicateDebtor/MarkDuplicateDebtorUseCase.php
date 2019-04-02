<?php

namespace App\Application\UseCase\MarkDuplicateDebtor;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MarkDuplicateDebtorUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $companiesService;

    public function __construct(
        CompaniesServiceInterface $companiesService
    ) {
        $this->companiesService = $companiesService;
    }

    public function execute(MarkDuplicateDebtorRequest ...$requests): void
    {
        $duplicates = array_map(function (MarkDuplicateDebtorRequest $request) {
            return (new MerchantDebtorDuplicateDTO())
                ->setParentDebtorId($request->getIsDuplicateOf())
                ->setDebtorId($request->getDebtorId());
        }, $requests);

        $this->companiesService->markDuplicates(... $duplicates);
    }
}
