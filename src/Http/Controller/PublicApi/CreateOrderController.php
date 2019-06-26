<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Post(
 *     path="/order",
 *     operationId="order_create",
 *     summary="Create Order",
 *     security={{"oauth2"={}}, {"apiKey"={}}},
 *
 *     tags={"Orders API"},
 *     x={"groups":{"public"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Merchant successfully created", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateOrderController
{
    private $createOrderUseCase;

    private $orderRequestFactory;

    private $orderResponseFactory;

    public function __construct(
        CreateOrderUseCase $createOrderUseCase,
        CreateOrderRequestFactory $orderRequestFactory,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = $this->orderRequestFactory
            ->createForCreateOrder($request);

        $orderContainer = $this->createOrderUseCase->execute($useCaseRequest);

        return new JsonResponse($this->orderResponseFactory->create($orderContainer)->toArray(), JsonResponse::HTTP_CREATED);
    }
}
