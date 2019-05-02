<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;

class GetMerchantDebtorUseCase
{
    private $merchantDebtorRepository;

    private $merchantDebtorResponseFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorResponseFactory $merchantDebtorResponseFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorResponseFactory = $merchantDebtorResponseFactory;
    }

    public function execute(GetMerchantDebtorRequest $request): GetMerchantDebtorResponse
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId(),
            []
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        return $this->merchantDebtorResponseFactory->create(
           $merchantDebtor,
           $request->getMerchantDebtorExternalId()
        );
    }
}
