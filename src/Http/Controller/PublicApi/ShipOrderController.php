<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Application\UseCase\ShipOrder\ShipOrderRequestV1;
use App\Application\UseCase\ShipOrder\ShipOrderUseCaseV1;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateInvoice\ShippingInfoFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 * @OA\Post(
 *     path="/order/{id}/ship",
 *     operationId="order_ship",
 *     summary="Ship Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"publicV1", "private"}},
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
 *          @OA\Schema(ref="#/components/schemas/ShipOrderRequestV1"))
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/OrderResponse"), description="Order successfully shipped. Order details."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ShipOrderController
{
    private ShipOrderUseCaseV1 $useCase;

    private ShippingInfoFactory $shippingInfoFactory;

    private UuidGeneratorInterface $uuidGenerator;

    public function __construct(
        ShipOrderUseCaseV1 $useCase,
        ShippingInfoFactory $shippingInfoFactory,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->useCase = $useCase;
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function execute(string $id, Request $request): LegacyOrderResponse
    {
        $generatedUuid = $this->uuidGenerator->uuid();
        $orderRequest = (new ShipOrderRequestV1(
            $id,
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
        ))
            ->setInvoiceUuid($generatedUuid)
            ->setShippingInfo($this->shippingInfoFactory->create($request, $generatedUuid))
            ->setExternalCode($request->request->get('external_order_id'))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceUrl($request->request->get('invoice_url'));

        try {
            return $this->useCase->execute($orderRequest);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException | ShipOrderMerchantFeeNotSetException $exception) {
            throw new BadRequestHttpException('Shipment is not allowed', $exception);
        }
    }
}
