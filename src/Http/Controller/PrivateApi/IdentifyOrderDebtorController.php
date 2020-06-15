<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2Request;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2Response;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2UseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/order/{uuid}/identify-debtor",
 *     operationId="order_identify_debtor",
 *     summary="Identify Order Debtor",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(type="string"), required=true, description="Order Uuid"),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/OrderDebtorIdentificationV2Response"), description="Debtor Entity"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class IdentifyOrderDebtorController
{
    private $useCase;

    public function __construct(OrderDebtorIdentificationV2UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid): OrderDebtorIdentificationV2Response
    {
        try {
            return $this->useCase->execute(new OrderDebtorIdentificationV2Request(null, $uuid));
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (DebtorNotIdentifiedException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
