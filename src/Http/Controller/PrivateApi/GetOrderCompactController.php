<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\GetOrderCompact\GetOrderCompactUseCase;
use App\DomainModel\Order\OrderNotFoundException;
use App\Http\Response\PrivateApi\GetOrderCompactResponsePayload;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/compact/orders/{uuid}",
 *     operationId="compact_order_get_details",
 *     summary="Get Order Details (Compact)",
 *
 *     tags={"Internal"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Order UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetOrderCompactResponse"), description="Order Details"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetOrderCompactController
{
    private GetOrderCompactUseCase $useCase;

    public function __construct(GetOrderCompactUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(UuidInterface $uuid): GetOrderCompactResponsePayload
    {
        try {
            $response = $this->useCase->execute($uuid);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new GetOrderCompactResponsePayload($response);
    }
}
