<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetMerchant\GetMerchantRequest;
use App\Application\UseCase\GetMerchant\GetMerchantUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetMerchantController
{
    private $useCase;

    public function __construct(GetMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $apiKey)
    {
        $request = new GetMerchantRequest($apiKey);
        $response = $this->useCase->execute($request);

        return new JsonResponse($response->getMerchantData());
    }
}
