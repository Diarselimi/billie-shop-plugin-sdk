<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtor;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMerchantDebtorController
{
    private $useCase;

    private $responseFactory;

    public function __construct(GetMerchantDebtorUseCase $useCase, MerchantDebtorResponseFactory $responseFactory)
    {
        $this->useCase = $useCase;
        $this->responseFactory = $responseFactory;
    }

    public function execute(string $merchantDebtorUuid, Request $request): MerchantDebtor
    {
        try {
            $container = $this->useCase->execute(new GetMerchantDebtorRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $merchantDebtorUuid,
                null
            ));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return $this->responseFactory->createFromContainer($container);
    }
}
