<?php

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\PaymentMethod\BankTransactionPaymentMethodResolver;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GetMerchantPaymentDetailsUseCase
{
    private MerchantRepository $merchantRepository;

    private PaymentsRepositoryInterface $paymentsRepository;

    private BankTransactionPaymentMethodResolver $paymentMethodResolver;

    private MerchantDebtorRepositoryInterface $merchantDebtorRepository;

    public function __construct(
        MerchantRepository $merchantRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        BankTransactionPaymentMethodResolver $paymentMethodResolver,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentMethodResolver = $paymentMethodResolver;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function execute(GetMerchantPaymentDetailsRequest $request): GetMerchantPaymentDetailsResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if ($merchant === null) {
            throw new MerchantNotFoundException();
        }

        $transactionDetails = $this->paymentsRepository->getPaymentDetails(
            $merchant->getPaymentUuid(),
            $request->getTransactionUuid()
        );

        $debtorPaymentUuid = $this->getDebtorPaymentUuid($transactionDetails->getMerchantDebtorUuid());
        $paymentMethod = $this->paymentMethodResolver->getPaymentMethod(
            $request->getTransactionUuid(),
            $debtorPaymentUuid
        );

        return new GetMerchantPaymentDetailsResponse($transactionDetails, $paymentMethod);
    }

    private function getDebtorPaymentUuid(?UuidInterface $merchantDebtorUuid): ?UuidInterface
    {
        if ($merchantDebtorUuid === null) {
            return null;
        }

        $merchantDebtor = $this->merchantDebtorRepository->getOneByUuid($merchantDebtorUuid);
        if ($merchantDebtor === null) {
            throw new MerchantDebtorNotFoundException();
        }

        if ($merchantDebtor->getPaymentDebtorId() === null) {
            return null;
        }

        return Uuid::fromString($merchantDebtor->getPaymentDebtorId());
    }
}
