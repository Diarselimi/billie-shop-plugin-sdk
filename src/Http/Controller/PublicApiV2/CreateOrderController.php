<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Http\Authentication\UserProvider;
use App\Http\Factory\OrderResponseFactory;
use App\Http\RequestTransformer\CreateOrder\CreateOrderRequestFactory;
use App\Http\Response\DTO\OrderDTO;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CREATE_ORDERS"})
 * @OA\Post(
 *     path="/orders",
 *     operationId="order_create_V2",
 *     summary="Create Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Back-end Order Creation"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=200, description="Order successfully created", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateOrderController
{
    private CreateOrderRequestFactory $requestFactory;

    private CreateOrderUseCase $useCase;

    private OrderResponseFactory $responseFactory;

    private UserProvider $userProvider;

    public function __construct(
        CreateOrderUseCase $useCase,
        CreateOrderRequestFactory $requestFactory,
        OrderResponseFactory $responseFactory,
        UserProvider $userProvider
    ) {
        $this->requestFactory = $requestFactory;
        $this->useCase = $useCase;
        $this->responseFactory = $responseFactory;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): OrderDTO
    {
        $merchantUser = $this->userProvider->getMerchantUser() ?? $this->userProvider->getMerchantApiUser();
        $useCaseInput = $this->requestFactory->createForCreateOrder($request);
        $useCaseInput->setMerchantId($merchantUser->getMerchant()->getId());

        return $this->responseFactory->create(
            $this->useCase->execute($useCaseInput)
        );
    }
}
