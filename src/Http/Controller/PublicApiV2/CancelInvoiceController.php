<?php

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\CancelInvoice\CancelInvoiceRequest;
use App\Application\UseCase\CancelInvoice\CancelInvoiceUseCase;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_CANCEL_INVOICES"})
 * @OA\Delete(
 *     path="/invoices/{uuid}",
 *     operationId="invoice_cancel_v2",
 *     summary="Cancel Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true, description="Invoice UUID"),
 *
 *     @OA\Response(response=204, description="Invoice successfully cancelled"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CancelInvoiceController
{
    private CancelInvoiceUseCase $useCase;

    private UserProvider $userProvider;

    public function __construct(CancelInvoiceUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(string $uuid): void
    {
        $merchant = $this->userProvider->getMerchantUser() ?? $this->userProvider->getMerchantApiUser();

        try {
            $this->useCase->execute(new CancelInvoiceRequest(
                $uuid,
                $merchant->getMerchant()->getId()
            ));
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (CreditNoteNotAllowedException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
