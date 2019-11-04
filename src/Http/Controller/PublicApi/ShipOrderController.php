<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ShipOrder\ShipOrderException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponse;
use App\Http\HttpConstantsInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 *
 * @OA\Post(
 *     path="/order/{id}/ship",
 *     operationId="order_ship",
 *     summary="Ship Order",
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
 *          @OA\Schema(ref="#/components/schemas/ShipOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/OrderResponse"), description="Order successfully shipped. Order details."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ShipOrderController
{
    private $useCase;

    public function __construct(ShipOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): OrderResponse
    {
        $orderRequest = (new ShipOrderRequest($id, $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)))
            ->setExternalCode($request->request->get('external_order_id'))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceUrl($request->request->get('invoice_url'))
            ->setShippingDocumentUrl($request->request->get('shipping_document_url'));

        try {
            return $this->useCase->execute($orderRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (OrderWorkflowException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (ShipOrderException $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Order can not be shipped", $exception);
        }
    }
}
