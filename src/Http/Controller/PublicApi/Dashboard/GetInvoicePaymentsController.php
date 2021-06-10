<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsRequest;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsUseCase;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponse;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_VIEW_ORDERS")
 *
 * @OA\Get(
 *     path="/invoices/{uuid}/payments",
 *     operationId="get_invoice_payments",
 *     summary="Get Invoice Payments",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Invoice UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetInvoicePaymentsResponse")
 *     ),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
final class GetInvoicePaymentsController
{
    private GetInvoicePaymentsUseCase $useCase;

    public function __construct(GetInvoicePaymentsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): GetInvoicePaymentsResponse
    {
        try {
            $useCaseRequest = new GetInvoicePaymentsRequest(
                $uuid,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );
            $response = $this->useCase->execute($useCaseRequest);
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $response;
    }
}
