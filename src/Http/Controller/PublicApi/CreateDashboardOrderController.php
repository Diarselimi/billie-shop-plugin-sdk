<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateOrder\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CREATE_ORDERS"})
 * @OA\Post(
 *     path="/order-dashboard",
 *     operationId="order_create_dashboard",
 *     summary="Create Order For Dashboard",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Order Creation"},
 *     x={"groups":{"private"}},
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
class CreateDashboardOrderController
{
    private const DEFAULT_LEGAL_FORM = '99999';

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

    public function execute(Request $request): OrderResponse
    {
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
            OrderEntity::CREATION_SOURCE_DASHBOARD
        );
        $debtorCompany = $request->request->get('debtor_company', []);
        $debtorCompany['legal_form'] = $debtorCompany['legal_form'] ? $debtorCompany['legal_form'] : self::DEFAULT_LEGAL_FORM;
        $request->request->set('debtor_company', $debtorCompany);

        $useCaseRequest = $this->orderRequestFactory->createForCreateOrder($request);

        return $this->createOrderUseCase->execute($useCaseRequest);
    }
}
