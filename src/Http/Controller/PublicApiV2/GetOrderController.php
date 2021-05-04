<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use App\Http\Factory\OrderResponseFactory;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Application\Exception\OrderNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_VIEW_ORDERS")
 * @OA\Get(
 *     path="/orders/{id}",
 *     operationId="order_get_details",
 *     summary="Get Order Details",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="id",
 *          @OA\Schema(oneOf={@OA\Schema(ref="#/components/schemas/UUID"), @OA\Schema(type="string")}),
 *          description="Order external code or UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/Order"), description="Order details"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetOrderController
{
    private GetOrderUseCase $useCase;

    private OrderResponseFactory $factory;

    public function __construct(GetOrderUseCase $useCase, OrderResponseFactory $factory)
    {
        $this->useCase = $useCase;
        $this->factory = $factory;
    }

    public function execute(string $id, Request $request): JsonResponse
    {
        try {
            $orderContainer = $this->useCase->execute(
                new GetOrderRequest($id, $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            );
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new JsonResponse(
            $this->factory->create($orderContainer)->toArray()
        );
    }
}
