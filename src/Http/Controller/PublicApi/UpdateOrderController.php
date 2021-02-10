<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderUseCase;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\UpdateOrder\UpdateOrderAmountRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 *
 * @OA\Patch(
 *     path="/order/{id}",
 *     operationId="order_update",
 *     summary="Update Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"public", "private"}},
 *
 *     @OA\Parameter(in="path", name="id",
 *          @OA\Schema(oneOf={@OA\Schema(ref="#/components/schemas/UUID"), @OA\Schema(type="string")}),
 *          description="Order external code or UUID",
 *          required=true
 *     ),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/LegacyUpdateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateOrderController
{
    private LegacyUpdateOrderUseCase $useCase;

    private UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory;

    public function __construct(
        LegacyUpdateOrderUseCase $useCase,
        UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->updateOrderAmountRequestFactory = $updateOrderAmountRequestFactory;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
            $orderRequest = (new LegacyUpdateOrderRequest($id, $merchantId))
                ->setAmount($this->updateOrderAmountRequestFactory->create($request))
                ->setDuration($request->request->get('duration'))
                ->setInvoiceNumber($request->request->get('invoice_number'))
                ->setInvoiceUrl($request->request->get('invoice_url'))
                ->setExternalCode($request->request->get('order_id'));

            $this->useCase->execute($orderRequest);
        } catch (UpdateOrderException | OrderBeingCollectedException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }
}
