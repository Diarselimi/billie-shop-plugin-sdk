<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtorExternalIds\GetMerchantDebtorExternalIdsRequest;
use App\Application\UseCase\GetMerchantDebtorExternalIds\GetMerchantDebtorExternalIdsUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorExternalIdsList;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CREATE_ORDERS"})
 * @OA\Get(
 *     path="/debtor/{uuid}/external-ids",
 *     operationId="debtor_get_external_ids",
 *     summary="Get Debtor External Ids",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Debtors"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantDebtorExternalIdsListResponse"), description="Debtor details"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantDebtorExternalIdsController
{
    private $useCase;

    public function __construct(GetMerchantDebtorExternalIdsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): MerchantDebtorExternalIdsList
    {
        try {
            $externalIds = $this->useCase->execute(new GetMerchantDebtorExternalIdsRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $uuid
            ));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return (new MerchantDebtorExternalIdsList())
            ->setTotal(count($externalIds))
            ->setItems($externalIds);
    }
}
