<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\UseCase\SaveMerchantFinancialAssessment\SaveMerchantFinancialAssessmentRequest;
use App\Application\UseCase\SaveMerchantFinancialAssessment\SaveMerchantFinancialAssessmentUseCase;
use App\Http\Authentication\UserProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @IsGranted("ROLE_MANAGE_ONBOARDING")
 *
 * @OA\Post(
 *     path="/merchant/financial-assessment",
 *     operationId="save_merchant_financial_assessment",
 *     summary="Save merchant financial assessment.",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/SaveMerchantFinancialAssessmentRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Financial Assessment saved."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=409, ref="#/components/responses/ResourceConflict"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class SaveMerchantFinancialAssessmentController
{
    private $useCase;

    private $userProvider;

    public function __construct(SaveMerchantFinancialAssessmentUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): void
    {
        $merchant = $this->userProvider->getUser()->getMerchant();
        $useCaseRequest = (new SaveMerchantFinancialAssessmentRequest($merchant->getId(), $merchant->getPaymentUuid()))
            ->setYearlyTransactionVolume($request->get('yearly_transaction_volume'))
            ->setMeanInvoiceAmount($request->get('mean_invoice_amount'))
            ->setCancellationRate($request->get('cancellation_rate'))
            ->setInvoiceDuration($request->get('invoice_duration'))
            ->setReturningOrderRate($request->get('returning_order_rate'))
            ->setDefaultRate($request->get('default_rate'))
            ->setHighInvoiceAmount($request->get('high_invoice_amount'))
            ->setDigitalGoodsRate($request->get('digital_goods_rate'))
        ;

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (MerchantOnboardingStepTransitionException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }
    }
}
