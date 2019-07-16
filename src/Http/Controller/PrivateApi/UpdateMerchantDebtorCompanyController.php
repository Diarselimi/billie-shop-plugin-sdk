<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyRequest;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/merchant-debtor/{debtorUuid}/update-company",
 *     operationId="update_merchant_debtor_company",
 *     summary="Updat Merchant Debtor Company",
 *
 *     tags={"Debtors"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="debtorUuid", description="Merchant-Debtor UUID", @OA\Schema(type="string"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateMerchantDebtorCompanyRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Merchant debtor company updated"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateMerchantDebtorCompanyController
{
    private $useCase;

    public function __construct(UpdateMerchantDebtorCompanyUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $debtorUuid): void
    {
        try {
            $request = (new UpdateMerchantDebtorCompanyRequest())
                ->setDebtorUuid($debtorUuid)
                ->setName($request->request->get('name'))
                ->setAddressHouse($request->request->get('address_house'))
                ->setAddressStreet($request->request->get('address_street'))
                ->setAddressCity($request->request->get('address_city'))
                ->setAddressPostalCode($request->request->get('address_postal_code'));

            $this->useCase->execute($request);
        } catch (MerchantDebtorNotFoundException $exception) {
            throw new NotFoundHttpException('Merchant debtor not found');
        }
    }
}
