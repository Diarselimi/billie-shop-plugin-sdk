<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetMerchant\GetMerchantRequest;
use App\Application\UseCase\GetMerchant\GetMerchantResponse;
use App\Application\UseCase\GetMerchant\GetMerchantUseCase;

class GetMerchantController
{
    private $useCase;

    public function __construct(GetMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $apiKey): GetMerchantResponse
    {
        $request = new GetMerchantRequest($apiKey);
        $response = $this->useCase->execute($request);

        return $response;
    }
}
