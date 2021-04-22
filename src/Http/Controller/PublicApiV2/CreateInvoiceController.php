<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\Http\HttpConstantsInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Http\RequestTransformer\AmountRequestFactory;
use OpenApi\Annotations as OA;
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
 *     @OA\Response(response=201, description="Invoice successfully created"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateInvoiceController
{
    private ShipOrderUseCase $shipOrderUseCase;

    private AmountRequestFactory $requestFactory;

    public function __construct(ShipOrderUseCase $shipOrderUseCase, AmountRequestFactory $requestFactory)
    {
        $this->shipOrderUseCase = $shipOrderUseCase;
        $this->requestFactory = $requestFactory;
    }

    public function execute(Request $request): void
    {
        $shipRequest = (new CreateInvoiceRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
        ))
            ->setExternalCode($request->request->get('external_code'))
            ->setInvoiceUrl($request->request->get('invoice_url'))
            ->setShippingDocumentUrl($request->request->get('shipping_document_url'))
            ->setAmount($this->requestFactory->create($request))
            ->setOrders($request->request->get('orders', []))
            ->setLineItems($request->request->get('line_items', []));

        try {
            $this->shipOrderUseCase->execute($shipRequest);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException | ShipOrderException $exception) {
            throw new BadRequestHttpException('Invoice could not be created.', $exception);
        }
    }
}
