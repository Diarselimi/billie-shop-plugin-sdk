<?php

declare(strict_types=1);

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\RegisterMerchant\RegisterMerchantRequest;
use App\Application\UseCase\RegisterMerchant\RegisterMerchantUseCase;
use App\DomainModel\DebtorCompany\IdentifyFirmenwissenFailedException;
use App\DomainModel\Merchant\CreateMerchantException;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * @OA\Post(
 *     path="/merchant/registration",
 *     operationId="merchant_registration",
 *     summary="Merchant Registration",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/RegisterMerchantRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Merchant registered successfully", @OA\JsonContent(ref="#/components/schemas/RegisterMerchantResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=409, ref="#/components/responses/ResourceConflict"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound", description="Company not found"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RegisterMerchantController
{
    private $useCase;

    public function __construct(RegisterMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = new RegisterMerchantRequest(
            $request->get('crefo_id'),
            $request->get('email')
        );

        try {
            $useCaseResponse = $this->useCase->execute($useCaseRequest);
        } catch (MerchantCompanyNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (IdentifyFirmenwissenFailedException $e) {
            throw new NotFoundHttpException('Firmenwissen identification failed');
        } catch (DuplicateMerchantCompanyException $e) {
            throw new ConflictHttpException($e->getMessage());
        } catch (CreateMerchantException $e) {
            throw new ServiceUnavailableHttpException(null, $e->getMessage());
        }

        return new JsonResponse($useCaseResponse->toArray(), JsonResponse::HTTP_CREATED);
    }
}
