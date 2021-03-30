<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CheckInvoiceOwner\CheckInvoiceOwnerRequest;
use App\Application\UseCase\CheckInvoiceOwner\CheckInvoiceOwnerUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_VIEW_ORDERS")
 *
 * @OA\Get(
 *     path="/invoices/{uuid}/check-owner",
 *     operationId="check_invoice_owner",
 *     summary="Check invoice owner",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(type="string"), required=true),
 *
 *     @OA\Response(response=204, description="Invoice is owned by merchant"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckInvoiceOwnerController
{
    private CheckInvoiceOwnerUseCase $useCase;

    public function __construct(CheckInvoiceOwnerUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): void
    {
        $request = new CheckInvoiceOwnerRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $uuid
        );

        $invoiceBelongsToMerchant = $this->useCase->execute($request);
        if (!$invoiceBelongsToMerchant) {
            throw new NotFoundHttpException();
        }
    }
}
