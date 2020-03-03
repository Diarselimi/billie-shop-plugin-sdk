<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\SynchronizeMerchantDebtor\SynchronizeMerchantDebtorUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SynchronizeMerchantDebtorController
{
    private $useCase;

    public function __construct(SynchronizeMerchantDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /**
     * @OA\Post(
     *     path="/merchant-debtor/{merchantDebtorUuid}/synchronize",
     *     operationId="synchronize_merchant_debtor",
     *     summary="Synchronize merchant_debtor address.",
     *
     *     tags={"Support"},
     *     x={"groups":{"private"}},
     *
     *     @OA\Parameter(
     *          in="path",
     *          name="merchantDebtorUuid",
     *          @OA\Schema(ref="#/components/schemas/UUID"),
     *          description="Mechant Debtor UUID",
     *          required=true
     *     ),
     *
     *     @OA\Response(response=200, description="Debtor synchronized successfully", @OA\JsonContent(ref="#/components/schemas/MerchantDebtorSynchronizationResponse")),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     *  )
     */
    public function execute(string $merchantDebtorUuid)
    {
        try {
            return $this->useCase->execute($merchantDebtorUuid);
        } catch (MerchantDebtorNotFoundException $debtorNotFoundException) {
            throw new NotFoundHttpException($debtorNotFoundException->getMessage());
        }
    }
}
