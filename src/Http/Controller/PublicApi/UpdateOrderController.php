<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
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
 *          @OA\Schema(ref="#/components/schemas/UpdateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateOrderController
{
    private $useCase;

    public function __construct(UpdateOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
            $orderRequest = (new UpdateOrderRequest($id, $merchantId))
                ->setAmount($this->createAmount($request))
                ->setDuration($request->request->get('duration'))
                ->setInvoiceNumber($request->request->get('invoice_number'))
                ->setInvoiceUrl($request->request->get('invoice_url'))
                ->setExternalCode($request->request->get('order_id'));

            $this->useCase->execute($orderRequest);
        } catch (UpdateOrderException | FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }

    private function createAmount(Request $request)
    {
        $amount = $request->request->get('amount');

        if (!is_array($amount)) {
            return null;
        }

        $gross = $amount['gross'] ?? null;
        $net = $amount['net'] ?? null;
        $tax = $amount['tax'] ?? null;

        if ($gross === null && $net === null && $tax === null) {
            return null;
        }

        return (new CreateOrderAmountRequest())
            ->setGross($gross)
            ->setNet($net)
            ->setTax($tax);
    }
}
