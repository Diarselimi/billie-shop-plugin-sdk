<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorExtended;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMerchantDebtorSupportController
{
    private $useCase;

    private $responseFactory;

    public function __construct(GetMerchantDebtorUseCase $useCase, MerchantDebtorResponseFactory $responseFactory)
    {
        $this->useCase = $useCase;
        $this->responseFactory = $responseFactory;
    }

    public function execute(int $merchantId, string $merchantDebtorExternalId): MerchantDebtorExtended
    {
        try {
            $container = $this->useCase->execute(new GetMerchantDebtorRequest($merchantId, null, $merchantDebtorExternalId));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return $this->responseFactory->createExtendedFromContainer($container);
    }
}
