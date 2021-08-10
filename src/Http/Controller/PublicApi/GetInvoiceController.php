<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoice\GetInvoiceRequest;
use App\Application\UseCase\GetInvoice\GetInvoiceResponse;
use App\Application\UseCase\GetInvoice\GetInvoiceUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Ozean12\InvoiceButler\Client\DomainModel\Invoice\InvoiceNotFoundException as ClientInvoiceNotFoundException;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_VIEW_INVOICES"})
 *
 * @OA\Get(
 *     path="/invoices/{uuid}",
 *     operationId="get_invoice",
 *     summary="Get Invoice",
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(type="string"), required=true, description="Get Invoice with Uuid"),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetInvoiceResponse"), description="Invoice Response"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
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

    public function execute(UuidInterface $uuid): GetInvoiceResponse
    {
        try {
            $useCaseRequest = new GetInvoiceRequest(
                $uuid,
                $this->userProvider->getAuthenticatedMerchantUser()->getMerchant()->getId()
            );

            return $this->useCase->execute($useCaseRequest);
        } catch (InvoiceNotFoundException | ClientInvoiceNotFoundException | MerchantUserNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
