<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorResponse;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;

class GetMerchantDebtorController
{
    private $useCase;

    public function __construct(GetMerchantDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(int $merchantId, string $merchantDebtorExternalId): GetMerchantDebtorResponse
    {
        try {
            $response = $this->useCase->execute(new GetMerchantDebtorRequest(
                $merchantDebtorExternalId,
                $merchantId
            ));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new PaellaCoreCriticalException(
                'Merchant debtor not found',
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        }

        return $response;
    }
}
