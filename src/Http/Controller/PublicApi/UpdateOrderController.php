<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/order/{id}",
 *     operationId="order_update",
 *     summary="Update Order",
 *     security={{"oauth2"={}}, {"apiKey"={}}},
 *
 *     tags={"Orders API"},
 *     x={"groups":{"public"}},
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
            $orderRequest = (new UpdateOrderRequest($id, $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)))
                ->setAmountGross($request->request->get('amount_gross'))
                ->setAmountNet($request->request->get('amount_net'))
                ->setAmountTax($request->request->get('amount_tax'))
                ->setDuration($request->request->get('duration'))
                ->setInvoiceNumber($request->request->get('invoice_number'))
                ->setInvoiceUrl($request->request->get('invoice_url'));

            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }
}
