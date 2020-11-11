<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Http\RequestTransformer\CreateOrder\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT"})
 * @OA\Post(
 *     path="/order",
 *     operationId="order_create",
 *     summary="Create Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Back-end Order Creation"},
 *     x={"groups":{"public"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=200, description="Order successfully created", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateOrderController
{
    private CreateOrderUseCase $createOrderUseCase;

    private CreateOrderRequestFactory $orderRequestFactory;

    public function __construct(
        CreateOrderUseCase $createOrderUseCase,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = $this->orderRequestFactory->createForCreateOrder($request);

        return new JsonResponse(
            $this->createOrderUseCase->execute($useCaseRequest)->toArray(),
            JsonResponse::HTTP_OK
        );
    }
}
