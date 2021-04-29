<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceRequest;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceUseCase;
use App\DomainModel\Invoice\InvalidDurationException;
use DomainException;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @OA\Post(
 *     path="/invoices/{uuid}/extend-duration",
 *     operationId="extend_invoice_duration",
 *     summary="Extend Invoice duration",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Invoices"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(properties={
 *                  @OA\Property(property="duration", ref="#/components/schemas/OrderDuration")
 *              })
 *          )
 *     ),
 *
 *     @OA\Response(response=204, description="Invoice extended successfully"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=422, description="Unprocessable Entity", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ExtendInvoiceController
{
    private ExtendInvoiceUseCase $extendInvoiceUseCase;

    public function __construct(ExtendInvoiceUseCase $extendInvoiceUseCase)
    {
        $this->extendInvoiceUseCase = $extendInvoiceUseCase;
    }

    public function execute(string $uuid, Request $request): void
    {
        $duration = (int) $request->get('duration');
        $extendInvoiceRequest = new ExtendInvoiceRequest($uuid, $duration);

        try {
            $this->extendInvoiceUseCase->execute($extendInvoiceRequest);
        } catch (InvoiceNotFoundException $exception) {
            throw new NotFoundHttpException('Invoice not found', $exception);
        } catch (InvalidDurationException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'Invalid duration',
                'duration',
                $extendInvoiceRequest->getDuration()
            );
        } catch (DomainException $exception) {
            throw new UnprocessableEntityHttpException('Invoice cannot be extended');
        }
    }
}
