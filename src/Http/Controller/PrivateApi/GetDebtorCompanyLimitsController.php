<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetDebtorCompanyLimits\GetDebtorCompanyLimitsRequest;
use App\Application\UseCase\GetDebtorCompanyLimits\GetDebtorCompanyLimitsResponse;
use App\Application\UseCase\GetDebtorCompanyLimits\GetDebtorCompanyLimitsUseCase;
use App\DomainModel\Merchant\MerchantNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/debtor-company/{uuid}/limits",
 *     operationId="get_debtor_company_limits",
 *     summary="Get Debtor Company Limits",
 *
 *     tags={"Debtors"},
 *     x={"groups":{"support", "salesforce"}},
 *
 *     @OA\Parameter(
 *          in="path",
 *          name="uuid",
 *          description="Company UUID",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          required=true,
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetDebtorCompanyLimitsResponse")
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetDebtorCompanyLimitsController
{
    private $useCase;

    public function __construct(GetDebtorCompanyLimitsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid): GetDebtorCompanyLimitsResponse
    {
        try {
            $useCaseRequest = new GetDebtorCompanyLimitsRequest($uuid);

            return $this->useCase->execute($useCaseRequest);
        } catch (MerchantDebtorNotFoundException | MerchantNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
