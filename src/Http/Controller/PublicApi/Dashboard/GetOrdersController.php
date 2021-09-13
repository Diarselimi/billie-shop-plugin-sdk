<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\Dashboard\GetOrders\GetOrdersRequest;
use App\Application\UseCase\Dashboard\GetOrders\GetOrdersUseCase;
use App\Http\HttpConstantsInterface;
use App\Http\ResponseTransformer\Dashboard\GetOrdersResponsePayload;
use App\Support\SearchInput;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_VIEW_ORDERS")
 *
 * @OA\Get(
 *     path="/orders",
 *     operationId="orders_get",
 *     summary="Get Orders",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Orders"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="sort_by", @OA\Schema(type="string",
 *          enum={
 *              "id", "id,desc", "id,asc",
 *          }, default="id,desc"), required=false),
 *
 *     @OA\Parameter(in="query", name="offset", @OA\Schema(type="integer", minimum=0), required=false),
 *     @OA\Parameter(in="query", name="limit", @OA\Schema(type="integer", minimum=1, maximum=100), required=false),
 *
 *     @OA\Parameter(in="query", name="search", description="Search text. Filters search results by: `external_code`, `uuid` or `invoice_number`.",
 *          @OA\Schema(ref="#/components/schemas/TinyText"), required=false),
 *
 *     @OA\Parameter(in="query", name="filters", style="deepObject", explode=true, @OA\Schema(type="object", properties={
 *          @OA\Property(property="merchant_debtor_id", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="state", type="array", @OA\Items(ref="#/components/schemas/OrderState"), minItems=1)
 *     }), required=false),
 *
 *     @OA\Response(
 *          response=200,
 *          @OA\JsonContent(ref="#/components/schemas/GetOrdersResponsePayload"),
 *          description="Orders list"
 *     ),
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

    public function execute(Request $request): GetOrdersResponsePayload
    {
        [$sortField, $sortDirection] = $this->parseSortBy($request->query);

        $searchString = $request->query->has('search') ?
            SearchInput::asString($request->query->get('search', '')) : null;

        $useCaseRequest = new GetOrdersRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetOrdersRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection ?: GetOrdersRequest::DEFAULT_SORT_DIRECTION),
            $searchString,
            $this->parseFilters($request)
        );

        $useCaseResponse = $this->useCase->execute($useCaseRequest);

        return new GetOrdersResponsePayload($useCaseResponse);
    }

    private function parseFilters(Request $request): array
    {
        $filters = $request->query->get('filters', []);

        return is_array($filters) ? $filters : [];
    }

    private function parseSortBy(ParameterBag $query): array
    {
        if (!$query->has('sort_by')) {
            return [GetOrdersRequest::DEFAULT_SORT_FIELD, GetOrdersRequest::DEFAULT_SORT_DIRECTION];
        }

        $sortBy = $query->get('sort_by');

        if (strpos($sortBy, ',') !== false) {
            return explode(',', $sortBy, 2);
        }

        return [$sortBy, GetOrdersRequest::DEFAULT_SORT_DIRECTION];
    }
}
