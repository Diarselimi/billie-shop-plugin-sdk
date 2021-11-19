<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\RequestValidationException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderAmountExceededException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderNoOrderUuidException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderOrderExternalCodeNotSetException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\AmountRequestFactory;
use App\Http\RequestTransformer\CreateInvoice\InvoiceLineItemsFactory;
use App\Http\RequestTransformer\CreateInvoice\ShippingInfoFactory;
use App\Infrastructure\CommandBus\SynchronousCommandBus\Decorators\DbTransactionCommandBusDecorator;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_SHIP_ORDERS")
 * @OA\Post(
 *     path="/invoices",
 *     operationId="invoice_create",
 *     summary="Create Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateInvoiceRequest"))
 *     ),
 *
 *     @OA\Response(
 *          response=201,
 *          description="Invoice successfully created",
 *          @OA\JsonContent(
 *              type="object",
 *              required={"uuid"},
 *              properties={
 *                  @OA\Property(property="uuid", ref="#/components/schemas/UUID")
 *              }
 *          )
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateInvoiceController
{
    private AmountRequestFactory $requestFactory;

    private InvoiceLineItemsFactory $lineItemsFactory;

    private DbTransactionCommandBusDecorator $synchronousCommandBus;

    private UuidGeneratorInterface $uuidGenerator;

    private ShippingInfoFactory $shippingInfoFactory;

    public function __construct(
        AmountRequestFactory $requestFactory,
        InvoiceLineItemsFactory $lineItemsFactory,
        DbTransactionCommandBusDecorator $synchronousCommandBus,
        UuidGeneratorInterface $uuidGenerator,
        ShippingInfoFactory $shippingInfoFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->lineItemsFactory = $lineItemsFactory;
        $this->synchronousCommandBus = $synchronousCommandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->shippingInfoFactory = $shippingInfoFactory;
    }

    public function execute(Request $request): JsonResponse
    {
        $generatedInvoiceUuid = $this->uuidGenerator->uuid();
        $shipRequest = (new CreateInvoiceRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $generatedInvoiceUuid,
            $this->shippingInfoFactory->create($request, $generatedInvoiceUuid)
        ))
            ->setExternalCode($request->request->get('external_code'))
            ->setInvoiceUrl($request->request->get('invoice_url'))
            ->setAmount($this->requestFactory->create($request))
            ->setOrders($request->request->get('orders', []))
            ->setLineItems($this->lineItemsFactory->create($request));

        try {
            $this->synchronousCommandBus->process($shipRequest);

            return new JsonResponse([
                'uuid' => $shipRequest->getInvoiceUuid(),
            ], JsonResponse::HTTP_CREATED);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (ShipOrderNoOrderUuidException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'One order uuid should be provided',
                'orders',
                ''
            );
        } catch (ShipOrderAmountExceededException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'Invoice amount should not exceed order unshipped amount',
                'amount',
                ''
            );
        } catch (ShipOrderOrderExternalCodeNotSetException $exception) {
            throw new BadRequestHttpException('Order external code should be set beforehand.', $exception);
        } catch (WorkflowException | ShipOrderMerchantFeeNotSetException $exception) {
            throw new BadRequestHttpException('Invoice could not be created.', $exception);
        }
    }
}
