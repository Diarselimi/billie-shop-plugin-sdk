<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsRequest;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsUseCase;
use App\DomainModel\Payment\BankTransactionNotFoundException;
use App\Http\HttpConstantsInterface;
use App\Http\Response\Dashboard\GetMerchantPaymentDetailsPayload;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_VIEW_PAYMENTS")
 *
 * @OA\Get(
 *     path="/payments/{uuid}",
 *     operationId="get_payment_details",
 *     summary="Get Payment Details",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Payments"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetMerchantPaymentDetailsResponse")
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
final class GetMerchantPaymentDetailsController
{
    private GetMerchantPaymentDetailsUseCase $useCase;

    public function __construct(GetMerchantPaymentDetailsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(UuidInterface $uuid, Request $request): GetMerchantPaymentDetailsPayload
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);

        $useCaseRequest = (new GetMerchantPaymentDetailsRequest($merchantId, $uuid));

        try {
            $useCaseResponse = $this->useCase->execute($useCaseRequest);
        } catch (MerchantNotFoundException | MerchantDebtorNotFoundException | BankTransactionNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new GetMerchantPaymentDetailsPayload($useCaseResponse);
    }
}
