<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\GetOrderPayments\GetOrderPaymentsUseCase;
use App\Support\PaginatedCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @IsGranted("ROLE_VIEW_ORDERS")
 *
 * @OA\Schema(schema="GetOrderPaymentsResponse", title="Order Payments Response", type="object", properties={
 *     @OA\Property(property="items", type="array", description="Order payment item",
 *      @OA\Items(ref="#/components/schemas/OrderPaymentDTO")),
 *     @OA\Property(property="total", type="integer", description="Total number of results"),
 * })
 *
 * @OA\Get(
 *     path="/order/{uuid}/payments",
 *     operationId="get_order_payments",
 *     summary="Get Order Payments",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Order UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetOrderPaymentsResponse")
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetOrderPaymentsController
{
    private $useCase;

    public function __construct(GetOrderPaymentsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid): PaginatedCollection
    {
        try {
            $response = $this->useCase->execute($uuid);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $response;
    }
}
