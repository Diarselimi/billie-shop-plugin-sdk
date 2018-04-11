<?php

namespace App\Application\UseCase\GetCustomer;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Customer\CustomerRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class GetCustomerUseCase
{
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(GetCustomerRequest $request): GetCustomerResponse
    {
        $apiKey = $request->getApiKey();
        $customer = $this->customerRepository->getOneByApiKeyRaw($apiKey);

        if (!$customer) {
            throw new PaellaCoreCriticalException(
                "Customer with api-key $apiKey not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        return new GetCustomerResponse($customer);
    }
}
