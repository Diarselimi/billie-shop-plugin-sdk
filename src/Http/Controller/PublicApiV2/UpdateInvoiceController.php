<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\Exception\InvoiceUpdateException;
use App\Application\UseCase\UpdateInvoice\UpdateInvoiceRequest;
use App\Application\UseCase\UpdateInvoice\UpdateInvoiceUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_UPDATE_INVOICES"})
 * @OA\Post(
 *     path="/invoices/{uuid}/update-details",
 *     operationId="invoice_update",
 *     summary="Update Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateInvoiceRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Invoice successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateInvoiceController
{
    private UpdateInvoiceUseCase $useCase;

    private UserProvider $userProvider;

    public function __construct(UpdateInvoiceUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(string $uuid, Request $request): void
    {
        try {
            $useCaseRequest = (new UpdateInvoiceRequest(
                $uuid,
                $this->userProvider->getAuthenticatedMerchantUser()->getMerchant()->getId()
            ))
                ->setExternalCode($request->request->get('external_code'))
                ->setInvoiceUrl($request->request->get('invoice_url'));
            $this->useCase->execute($useCaseRequest);
        } catch (InvoiceUpdateException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (InvoiceNotFoundException | MerchantUserNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
