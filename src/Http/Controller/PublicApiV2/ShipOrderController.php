<?php

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\AmountRequestFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 *
 */
class ShipOrderController
{
    private ShipOrderUseCase $useCase;

    private AmountRequestFactory $amountRequestFactory;

    public function __construct(ShipOrderUseCase $useCase, AmountRequestFactory $amountRequestFactory)
    {
        $this->useCase = $useCase;
        $this->amountRequestFactory = $amountRequestFactory;
    }

    public function execute(string $id, Request $request): OrderResponse
    {
        $orderRequest = (new ShipOrderRequest(
            $id,
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
        ))
            ->setExternalCode($request->request->get('external_order_id'))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceUrl($request->request->get('invoice_url'))
            ->setShippingDocumentUrl($request->request->get('shipping_document_url'))
            ->setAmount($this->amountRequestFactory->create($request))
            ->setDuration($request->request->has('duration') ? $request->request->getInt('duration') : null)
        ;

        try {
            return $this->useCase->execute($orderRequest);
        } catch (OrderContainerFactoryException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (WorkflowException | ShipOrderException $exception) {
            throw new BadRequestHttpException('Shipment is not allowed', $exception);
        }
    }
}
