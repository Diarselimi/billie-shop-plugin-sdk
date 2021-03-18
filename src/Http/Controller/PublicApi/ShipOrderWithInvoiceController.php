<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrderWithInvoice\ShipOrderWithInvoiceRequest;
use App\Application\UseCase\ShipOrderWithInvoice\ShipOrderWithInvoiceUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\UpdateOrder\UpdateOrderAmountRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_SHIP_ORDERS")
 * @OA\Post(
 *     path="/order/{uuid}/ship-with-invoice",
 *     operationId="order_ship_with_invoice",
 *     summary="Ship Order With Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), description="Order UUID", required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="multipart/form-data",
 *          @OA\Schema(ref="#/components/schemas/ShipOrderWithInvoiceRequest"))
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/OrderResponse"), description="Order successfully shipped. Order details."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ShipOrderWithInvoiceController
{
    private ShipOrderWithInvoiceUseCase $useCase;

    private UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory;

    public function __construct(
        ShipOrderWithInvoiceUseCase $useCase,
        UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->updateOrderAmountRequestFactory = $updateOrderAmountRequestFactory;
    }

    public function execute(string $uuid, Request $request): OrderResponse
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $orderRequest = (new ShipOrderWithInvoiceRequest($uuid, $merchantId))
            ->setExternalCode($request->request->get('external_order_id'))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceFile($request->files->get('invoice_file'));

        if ($request->request->has('amount')) {
            $amount = $this->updateOrderAmountRequestFactory->create($request);
            if ($amount === null) {
                throw new BadRequestHttpException('Invalid amount value supplied');
            }

            $orderRequest->setAmount($amount);
        }

        try {
            return $this->useCase->execute($orderRequest);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException | ShipOrderException $exception) {
            throw new BadRequestHttpException('Shipment is not allowed', $exception);
        }
    }
}
