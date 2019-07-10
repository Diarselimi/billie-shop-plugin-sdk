<?php

namespace App\Application\UseCase\GetMerchant;

use App\DomainModel\Merchant\MerchantRepositoryInterface;

class GetMerchantUseCase
{
    private $merchantRepository;

    public function __construct(MerchantRepositoryInterface $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(GetMerchantRequest $request): GetMerchantResponse
    {
        $id = $request->getId();
        $merchant = $this->merchantRepository->getOneById($id);

        if (!$merchant) {
            throw new MerchantNotFoundException("Merchant with id $id not found");
        }

        return new GetMerchantResponse($merchant);
    }
}
