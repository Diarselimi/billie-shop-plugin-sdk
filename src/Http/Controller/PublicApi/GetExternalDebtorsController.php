<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsResponse;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsRequest;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsUseCase;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsUseCaseException;
use App\Http\HttpConstantsInterface;
use App\Support\SearchInput;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted({"ROLE_CREATE_ORDERS", "ROLE_AUTHENTICATED_AS_CHECKOUT_USER"})
 * @OA\Get(
 *     path="/external-debtors",
 *     operationId="external_debtors_get",
 *     summary="Get External Debtors",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Debtors", "Checkout Client"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="search", description="Search text.", @OA\Schema(ref="#/components/schemas/TinyText"), required=true),
 *     @OA\Parameter(in="query", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=false),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetExternalDebtorsResponse"), description="External Debtors List"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetExternalDebtorsController
{
    private const ITEMS_LIMIT = 5;

    private $useCase;

    public function __construct(GetExternalDebtorsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): GetExternalDebtorsResponse
    {
        $useCaseRequest = new GetExternalDebtorsRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            SearchInput::asString($request->query->get('search', ''), true, 200),
            self::ITEMS_LIMIT
        );

        try {
            return $this->useCase->execute($useCaseRequest);
        } catch (GetExternalDebtorsUseCaseException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
