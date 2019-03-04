<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitRequest;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class UpdateMerchantDebtorLimitController
{
    private $useCase;

    public function __construct(UpdateMerchantDebtorLimitUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $merchantDebtorExternalId): void
    {
        try {
            $this->useCase->execute(new UpdateMerchantDebtorLimitRequest(
                $merchantDebtorExternalId,
                $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER),
                $request->request->get('limit')
            ));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new PaellaCoreCriticalException(
                'Merchant debtor not found',
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        }
    }
}
