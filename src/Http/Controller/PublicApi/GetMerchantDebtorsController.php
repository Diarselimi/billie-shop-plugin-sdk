<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsRequest;
use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Get(
 *     path="/debtors",
 *     operationId="debtors_get",
 *     summary="Get Debtors",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Debtors"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="sort_by", @OA\Schema(type="string",
 *          enum={
 *              "created_at", "created_at,desc", "created_at,asc",
 *              "external_code", "external_code,desc", "external_code,asc"
 *          }, default="created_at,desc"), required=false),
 *
 *     @OA\Parameter(in="query", name="offset", @OA\Schema(type="integer", minimum=0), required=false),
 *     @OA\Parameter(in="query", name="limit", @OA\Schema(type="integer", minimum=1, maximum=100), required=false),
 *     @OA\Parameter(in="query", name="search", description="Search text. Filters search results by: `external_code`.",
 *          @OA\Schema(ref="#/components/schemas/TinyText"), required=false),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantDebtorListResponse"), description="Debtors list"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantDebtorsController
{
    private $useCase;

    public function __construct(GetMerchantDebtorsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): MerchantDebtorList
    {
        [$sortField, $sortDirection] = ($request->query->has('sort_by')) ?
            explode(',', $request->query->get('sort_by')) :
            [GetMerchantDebtorsRequest::DEFAULT_SORT_FIELD, GetMerchantDebtorsRequest::DEFAULT_SORT_DIRECTION];

        $useCaseRequest = new GetMerchantDebtorsRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetMerchantDebtorsRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection ?: GetMerchantDebtorsRequest::DEFAULT_SORT_DIRECTION),
            trim($request->query->get('search'))
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
