<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CreatePreApproveOrder\CreatePreApprovedOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponse;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/order/pre-approve",
 *     operationId="pre_approve_create_order",
 *     summary="Pre-approved Order Create",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Creation"},
 *     x={"groups":{"support"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Order was created, but a confirmation is needed.", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreatePreApprovedOrderController
{
    private $useCase;

    private $orderRequestFactory;

    public function __construct(
        CreatePreApprovedOrderUseCase $useCase,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request): OrderResponse
    {
        $orderRequest = $this->orderRequestFactory->createForCreateOrder($request);

        return $this->useCase->execute($orderRequest);
    }
}
