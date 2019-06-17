<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CreateOrderInvoice\CreateOrderInvoiceRequest;
use App\Application\UseCase\CreateOrderInvoice\CreateOrderInvoiceUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/merchant/{merchantId}/order/{id}/invoice",
 *     operationId="order_create_invoice",
 *     summary="Create Order Invoice",
 *     description="Creates a new invoice, linking an order with a file. Called by the invoice uploader Lambda services.",
 *     security={{"oauth2"={}}, {"apiKey"={}}},
 *
 *     tags={"Internal API for Services"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="id", @OA\Schema(type="integer"), required=true, description="Order ID"),
 *     @OA\Parameter(in="path", name="merchantId", @OA\Schema(type="integer"), required=true, description="Merchant ID"),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", required={"file_id", "invoice_number"}, properties={
 *              @OA\Property(property="file_id", type="integer", description="File ID in the Nachos file service."),
 *              @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", description="Invoice number provided by the merchant.")
 *          }))
 *     ),
 *
 *     @OA\Response(response=201, description="Order invoice successfully created"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateOrderInvoiceController
{
    private $createOrderInvoiceUseCase;

    public function __construct(CreateOrderInvoiceUseCase $createOrderInvoiceUseCase)
    {
        $this->createOrderInvoiceUseCase = $createOrderInvoiceUseCase;
    }

    public function execute(int $merchantId, string $id, Request $request): JsonResponse
    {
        try {
            $useCaseRequest = new CreateOrderInvoiceRequest(
                $id,
                $merchantId,
                $request->request->getInt('file_id'),
                $request->request->get('invoice_number')
            );

            $this->createOrderInvoiceUseCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
