<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantUsers\GetMerchantUsersRequest;
use App\Application\UseCase\GetMerchantUsers\GetMerchantUsersResponse;
use App\Application\UseCase\GetMerchantUsers\GetMerchantUsersUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted({"ROLE_VIEW_USERS", "ROLE_MANAGE_USERS"})
 *
 * @OA\Get(
 *     path="/merchant/users",
 *     operationId="get_merchant_users",
 *     summary="Get Merchant Users",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="sort_by", @OA\Schema(type="string",
 *          enum={
 *              "invitation_status", "invitation_status,desc", "invitation_status,asc",
 *              "created_at", "created_at,desc", "created_at,asc",
 *          }, default="invitation_status"), required=false),
 *
 *     @OA\Parameter(in="query", name="offset", @OA\Schema(type="integer", minimum=0), required=false),
 *     @OA\Parameter(in="query", name="limit", @OA\Schema(type="integer", minimum=1, maximum=100), required=false),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetMerchantUsersResponse"), description="Users list"),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantUsersController
{
    private $useCase;

    public function __construct(GetMerchantUsersUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): GetMerchantUsersResponse
    {
        [$sortField, $sortDirection] = ($request->query->has('sort_by')) ?
            explode(',', $request->query->get('sort_by')) :
            [GetMerchantUsersRequest::DEFAULT_SORT_FIELD, GetMerchantUsersRequest::DEFAULT_SORT_DIRECTION];

        $useCaseRequest = new GetMerchantUsersRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetMerchantUsersRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection ?: GetMerchantUsersRequest::DEFAULT_SORT_DIRECTION)
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
