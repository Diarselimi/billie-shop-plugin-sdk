<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentNotAllowedException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentRequest;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentUseCase;
use App\Application\UseCase\ConfirmInvoicePayment\AmountExceededException;
use App\Http\Controller\MerchantIdTrait;
use OpenApi\Annotations as OA;
use Ozean12\Money\Money;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CONFIRM_ORDER_PAYMENT"})
 * @OA\Post(
 *     path="/invoices/{uuid}/confirm-payment",
 *     operationId="invoice_payment_confirm",
 *     summary="Confirm Invoice Payment",
 *     description="Confirms that the debtor paid the given amount of this invoice directly to the merchant.",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="paid_amount", type="number", format="float", minimum=0.1, description="The amount paid to the merchant from debtor.")
 *          }))
 *     ),
 *
 *     @OA\Response(response=204, description="Invoice payment successfully confirmed"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ConfirmInvoicePaymentController
{
    use MerchantIdTrait;

    private ConfirmInvoicePaymentUseCase $useCase;

    public function __construct(ConfirmInvoicePaymentUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): void
    {
        $useCaseRequest = new ConfirmInvoicePaymentRequest(
            $uuid,
            $this->getMerchantId($request),
            new Money($request->request->get('paid_amount'))
        );

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (InvoiceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        } catch (ConfirmInvoicePaymentNotAllowedException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception, JsonResponse::HTTP_FORBIDDEN);
        } catch (AmountExceededException $exception) {
            throw RequestValidationException::createForInvalidValue(
                $exception->getMessage(),
                'paid_amount',
                $useCaseRequest->getPaidAmount()->getMoneyValue()
            );
        }
    }
}
