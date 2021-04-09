<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteRequest;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteUseCase;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountTaxExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\InvoiceNotFoundException;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\UpdateOrder\UpdateOrderAmountRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_UPDATE_ORDERS"})
 * @OA\Post(
 *     path="/invoices/{uuid}/credit-notes",
 *     operationId="create_credit_note",
 *     summary="Create Credit Note",
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
 *          @OA\Schema(ref="#/components/schemas/CreateCreditNoteRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Credit note successfully created"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateCreditNoteController
{
    private CreateCreditNoteUseCase $useCase;

    private UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory;

    public function __construct(
        CreateCreditNoteUseCase $useCase,
        UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->updateOrderAmountRequestFactory = $updateOrderAmountRequestFactory;
    }

    public function execute(string $uuid, Request $request): void
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $useCaseRequest = (new CreateCreditNoteRequest())
            ->setInvoiceUuid($uuid)
            ->setMerchantId($merchantId)
            ->setAmount($this->updateOrderAmountRequestFactory->create($request))
            ->setExternalCode($request->request->get('external_code'))
            ->setExternalComment($request->request->get('comment'));

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (InvoiceNotFoundException $exception) {
            throw new NotFoundHttpException('Invoice not found', $exception);
        } catch (CreditNoteNotAllowedException $exception) {
            throw new AccessDeniedHttpException('Credit notes not allowed for this invoice', $exception);
        } catch (CreditNoteAmountExceededException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'Amount gross exceeded',
                'amount.gross',
                $useCaseRequest->getAmount()->getGross()->getMoneyValue()
            );
        } catch (CreditNoteAmountTaxExceededException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'Amount tax exceeded',
                'amount.tax',
                $useCaseRequest->getAmount()->getTax()->getMoneyValue()
            );
        }
    }
}
