<?php

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantPayment\MerchantPaymentResponseTransformer;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\Infrastructure\Repository\MerchantRepository;

class GetMerchantPaymentDetailsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $paymentsRepository;

    private $paymentFactory;

    public function __construct(
        MerchantRepository $merchantRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        MerchantPaymentResponseTransformer $paymentFactory
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentFactory = $paymentFactory;
    }

    public function execute(GetMerchantPaymentDetailsRequest $request): array
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $result = $this->paymentsRepository->get($merchant->getPaymentMerchantId(), $request->getTransactionUuid());

        if (empty($result)) {
            throw new TransactionNotFoundException("Transaction {$request->getTransactionUuid()} was not found.");
        }

        return $this->paymentFactory->expandPaymentItem($result);
    }
}
