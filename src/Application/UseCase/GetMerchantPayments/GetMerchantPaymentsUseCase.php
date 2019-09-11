<?php

namespace App\Application\UseCase\GetMerchantPayments;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantPayment\MerchantPaymentResponseFactory;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\SearchPaymentsDTOFactory;
use App\Infrastructure\Repository\MerchantRepository;

class GetMerchantPaymentsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $paymentsRepository;

    private $paymentFactory;

    private $paymentsDTOFactory;

    private $merchantDebtorRepository;

    public function __construct(
        MerchantRepository $merchantRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        MerchantPaymentResponseFactory $paymentFactory,
        SearchPaymentsDTOFactory $paymentsDTOFactory,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentFactory = $paymentFactory;
        $this->paymentsDTOFactory = $paymentsDTOFactory;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function execute(GetMerchantPaymentsRequest $request): GetMerchantPaymentsResponse
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

        $request->setMerchantPaymentUuid($merchant->getPaymentMerchantId());

        $result = $this->paymentsRepository->search($this->paymentsDTOFactory->create($request));

        return $this->paymentFactory->createFromGraphql($result);
    }
}
