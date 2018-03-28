<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetCustomer\GetCustomerRequest;
use App\Application\UseCase\GetCustomer\GetCustomerUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetCustomerController
{
    private $useCase;

    public function __construct(GetCustomerUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $apiKey)
    {
        $request = new GetCustomerRequest($apiKey);
        $response = $this->useCase->execute($request);

        return new JsonResponse($response->getCustomerData());
    }
}
