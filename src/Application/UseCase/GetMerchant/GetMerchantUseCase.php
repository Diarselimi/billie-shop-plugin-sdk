<?php

namespace App\Application\UseCase\GetMerchant;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class GetMerchantUseCase
{
    private $merchantRepository;

    public function __construct(MerchantRepositoryInterface $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(GetMerchantRequest $request): GetMerchantResponse
    {
        $apiKey = $request->getApiKey();
        $merchant = $this->merchantRepository->getOneByApiKeyRaw($apiKey);

        if (!$merchant) {
            throw new PaellaCoreCriticalException(
                "Merchant with api-key $apiKey not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        return new GetMerchantResponse($merchant);
    }
}
