<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrderWithInvoice\ShipOrderWithInvoiceRequestV1;
use App\Application\UseCase\ShipOrderWithInvoice\ShipOrderWithInvoiceUseCaseV1;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\OrderResponseV1;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\Http\HttpConstantsInterface;
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
 *          @OA\Schema(ref="#/components/schemas/ShipOrderWithInvoiceRequestV1"))
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
    private ShipOrderWithInvoiceUseCaseV1 $useCase;

    public function __construct(ShipOrderWithInvoiceUseCaseV1 $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): OrderResponseV1
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $orderRequest = (new ShipOrderWithInvoiceRequestV1($uuid, $merchantId))
            ->setExternalCode($request->request->get('external_order_id'))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceFile($request->files->get('invoice_file'));

        try {
            return $this->useCase->execute($orderRequest);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException | ShipOrderException $exception) {
            throw new BadRequestHttpException('Shipment is not allowed', $exception);
        }
    }
}
