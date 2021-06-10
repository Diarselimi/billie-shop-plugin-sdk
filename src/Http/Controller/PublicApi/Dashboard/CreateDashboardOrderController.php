<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\CreateOrder\LegacyCreateOrderRequest;
use App\Application\UseCase\CreateOrder\LegacyCreateOrderUseCase;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateOrder\CreateOrderRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
 *          @OA\Schema(ref="#/components/schemas/LegacyCreateOrderRequest"))
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

    private LegacyCreateOrderUseCase $createOrderUseCase;

    private CreateOrderRequestFactory $orderRequestFactory;

    public function __construct(
        LegacyCreateOrderUseCase $createOrderUseCase,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request): LegacyOrderResponse
    {
        return $this->createOrderUseCase->execute($this->buildUseCaseRequest($request));
    }

    private function buildUseCaseRequest(Request $request): LegacyCreateOrderRequest
    {
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
            OrderEntity::CREATION_SOURCE_DASHBOARD
        );

        $debtorCompany = $request->request->get('debtor_company', ['legal_form' => null]);
        $debtorCompany['legal_form'] = $debtorCompany['legal_form'] ?: self::DEFAULT_LEGAL_FORM;
        $request->request->set('debtor_company', $debtorCompany);

        $useCaseRequest = $this->orderRequestFactory->createForLegacyCreateOrder($request);

        $workflowName = $request->request->get('workflow_name');

        if (in_array($workflowName, [OrderEntity::WORKFLOW_NAME_V1, OrderEntity::WORKFLOW_NAME_V2], true)) {
            $useCaseRequest->setWorkflowName($workflowName);
        }

        return $useCaseRequest;
    }
}
