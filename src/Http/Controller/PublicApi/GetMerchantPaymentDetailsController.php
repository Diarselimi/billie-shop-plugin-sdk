<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsRequest;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsUseCase;
use App\Application\UseCase\GetMerchantPaymentDetails\TransactionNotFoundException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_VIEW_PAYMENTS")
 *
 * @OA\Schema(schema="GetMerchantPaymentDetailsResponse", title="Merchant Payments Response", type="object", properties={
 *   @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *   @OA\Property(property="amount", ref="#/components/schemas/Money"),
 *   @OA\Property(property="transaction_date", ref="#/components/schemas/Date"),
 *   @OA\Property(property="is_allocated", type="boolean"),
 *   @OA\Property(property="overpaid_amount", ref="#/components/schemas/Money"),
 *   @OA\Property(property="transaction_counterparty_iban", ref="#/components/schemas/IBAN"),
 *   @OA\Property(property="transaction_counterparty_name", type="string"),
 *   @OA\Property(property="transaction_reference", type="string"),
 *   @OA\Property(property="merchant_debtor_uuid", ref="#/components/schemas/UUID"),
 *   @OA\Property(property="orders", type="array", @OA\Items(type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="amount", ref="#/components/schemas/Money"),
 *      @OA\Property(property="outstanding_amount", ref="#/components/schemas/Money"),
 *      @OA\Property(property="external_id", type="string"),
 *      @OA\Property(property="invoice_number", type="string")
 *   })),
 * })
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
class GetMerchantPaymentDetailsController
{
    private $useCase;

    public function __construct(
        GetMerchantPaymentDetailsUseCase $useCase
    ) {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): JsonResponse
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);

        $useCaseRequest = (new GetMerchantPaymentDetailsRequest())
            ->setMerchantId($merchantId)
            ->setTransactionUuid($uuid);

        try {
            $response = $this->useCase->execute($useCaseRequest);
        } catch (MerchantNotFoundException | MerchantDebtorNotFoundException | TransactionNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new JsonResponse($response);
    }
}
