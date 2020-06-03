<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceUseCase;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\UpdateOrder\UpdateOrderAmountRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_UPDATE_ORDERS")
 *
 * @OA\Post(
 *     path="/order-with-invoice/{uuid}",
 *     operationId="order_update_with_invoice",
 *     summary="Update Order With Invoice",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Order UUID",
 *          required=true
 *     ),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="multipart/form-data",
 *          @OA\Schema(ref="#/components/schemas/UpdateOrderWithInvoiceRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateOrderWithInvoiceController
{
    private $useCase;

    private $updateOrderAmountRequestFactory;

    public function __construct(
        UpdateOrderWithInvoiceUseCase $useCase,
        UpdateOrderAmountRequestFactory $updateOrderAmountRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->updateOrderAmountRequestFactory = $updateOrderAmountRequestFactory;
    }

    public function execute(string $uuid, Request $request): void
    {
        try {
            $amount = $this->updateOrderAmountRequestFactory->create($request);
            if ($amount === null) {
                throw new BadRequestHttpException('Invalid amount value supplied');
            }

            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);

            $orderRequest = (new UpdateOrderWithInvoiceRequest($uuid, $merchantId))
                ->setAmount($amount)
                ->setInvoiceNumber($request->request->get('invoice_number'))
                ->setInvoiceFile($request->files->get('invoice_file'));
            $this->useCase->execute($orderRequest);
        } catch (UpdateOrderException | FraudOrderException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }
}
