<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantRoles\GetMerchantRolesResponse;
use App\Application\UseCase\GetMerchantRoles\GetMerchantRolesUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_MANAGE_USERS"})
 *
 * @OA\Get(
 *     path="/merchant/roles",
 *     operationId="get_merchant_roles",
 *     summary="Get merchant roles",
 *     description="List of all available user roles for the current merchant.",
 *     security={{"oauth2"={}}},
 *
 *     tags={"User Management"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetMerchantRolesResponse")
 *     ),
 *
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantRolesController
{
    private $useCase;

    public function __construct(GetMerchantRolesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): GetMerchantRolesResponse
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);

        return $this->useCase->execute($merchantId);
    }
}
