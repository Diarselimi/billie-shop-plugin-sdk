<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoice\GetInvoiceRequest;
use App\Application\UseCase\GetInvoice\GetInvoiceResponse;
use App\Application\UseCase\GetInvoice\GetInvoiceUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_VIEW_ORDERS")
 *
 * @OA\Get(
 *     path="/invoices/{uuid}",
 *     operationId="get_invoice",
 *     summary="Get Invoice",
 *
 *     tags={"Dashboard Orders"},
 *     x={"groups":{"public"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(type="string"), required=true, description="Get Invoice with Uuid"),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetInvoiceResponse"), description="Invoice Response"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetInvoiceController
{
    private GetInvoiceUseCase $useCase;

    private UserProvider $userProvider;

    public function __construct(GetInvoiceUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(string $uuid): GetInvoiceResponse
    {
        $useCaseRequest = new GetInvoiceRequest($uuid, $this->userProvider->getUser()->getMerchant()->getId());

        try {
            return $this->useCase->execute($useCaseRequest);
        } catch (InvoiceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
