<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetOrders\GetOrdersRequest;
use App\Application\UseCase\GetOrders\GetOrdersUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Get(
 *     path="/orders",
 *     operationId="orders_get",
 *     summary="Get Orders",
 *     security={{"oauth2"={}}, {"apiKey"={}}},
 *
 *     tags={"Orders API", "Dashboard API"},
 *     x={"groups":{"public"}},
 *
 *     @OA\Parameter(in="query", name="sort_by", @OA\Schema(type="string",
 *          enum={
 *              "created_at", "created_at,desc", "created_at,asc",
 *              "external_code", "external_code,desc", "external_code,asc",
 *              "amount_gross", "amount_gross,desc", "amount_gross,asc",
 *              "state", "state,desc", "state,asc",
 *          }, default="created_at,desc"), required=false),
 *
 *     @OA\Parameter(in="query", name="offset", @OA\Schema(type="integer", minimum=0), required=false),
 *     @OA\Parameter(in="query", name="limit", @OA\Schema(type="integer", minimum=1, maximum=100), required=false),
 *
 *     @OA\Parameter(in="query", name="search", description="Search text. Filters search results by: `external_code`, `uuid` or `invoice_number`.",
 *          @OA\Schema(ref="#/components/schemas/TinyText"), required=false),
 *
 *     @OA\Parameter(in="query", name="filters", style="deepObject", @OA\Schema(type="object", properties={
 *          @OA\Property(property="merchant_debtor_id", ref="#/components/schemas/UUID")
 *     }), required=false),
 *
 *     @OA\Response(response=200, @OA\JsonContent(type="object", properties={
 *          @OA\Property(property="total", type="integer", minimum=0),
 *          @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderResponse"))
 *     }), description="Order results"),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetOrdersController
{
    private $useCase;

    public function __construct(GetOrdersUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        [$sortField, $sortDirection] = ($request->query->has('sort_by')) ?
            explode(',', $request->query->get('sort_by')) :
            [GetOrdersRequest::DEFAULT_SORT_FIELD, GetOrdersRequest::DEFAULT_SORT_DIRECTION];

        $useCaseRequest = new GetOrdersRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetOrdersRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection ?: GetOrdersRequest::DEFAULT_SORT_DIRECTION),
            $request->query->get('search'),
            $request->query->get('filters')
        );

        $useCaseResponse = $this->useCase->execute($useCaseRequest);

        return new JsonResponse($useCaseResponse->toArray());
    }
}
