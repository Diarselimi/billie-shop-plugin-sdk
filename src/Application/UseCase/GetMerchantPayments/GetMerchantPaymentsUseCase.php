<?php

namespace App\Application\UseCase\GetMerchantPayments;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\SearchPaymentsDTOFactory;
use App\Infrastructure\Repository\MerchantRepository;
use App\Support\PaginatedCollection;

class GetMerchantPaymentsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $paymentsRepository;

    private $paymentsDTOFactory;

    private $merchantDebtorRepository;

    public function __construct(
        MerchantRepository $merchantRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        SearchPaymentsDTOFactory $paymentsDTOFactory,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentsDTOFactory = $paymentsDTOFactory;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function execute(GetMerchantPaymentsRequest $request): PaginatedCollection
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        if ($request->getMerchantDebtorUuid() !== null) {
            $merchantDebtor = $this->merchantDebtorRepository->getOneByUuid($request->getMerchantDebtorUuid());

            if (!$merchantDebtor) {
                throw new MerchantDebtorNotFoundException();
            }

            $request->setPaymentDebtorUuid($merchantDebtor->getPaymentDebtorId());
        }

        $request->setMerchantPaymentUuid($merchant->getPaymentUuid());

        return $this->paymentsRepository->searchMerchantPayments($this->paymentsDTOFactory->create($request));
    }
}
