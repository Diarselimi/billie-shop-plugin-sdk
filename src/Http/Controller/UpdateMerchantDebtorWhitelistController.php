<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\UpdateMerchantDebtorWhitelist\WhitelistMerchantDebtorRequest;
use App\Application\UseCase\UpdateMerchantDebtorWhitelist\WhitelistMerchantDebtorUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateMerchantDebtorWhitelistController
{
    private $useCase;

    public function __construct(WhitelistMerchantDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, int $merchantId, string $merchantDebtorExternalId): void
    {
        try {
            $this->useCase->execute(
                new WhitelistMerchantDebtorRequest(
                    $merchantDebtorExternalId,
                    $merchantId,
                    $request->request->getBoolean('is_whitelisted')
                )
            );
        } catch (MerchantDebtorNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), null, Response::HTTP_NOT_FOUND);
        }
    }
}
