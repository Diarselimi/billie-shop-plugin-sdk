<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsRequest;
use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsUseCase;
use App\Http\HttpConstantsInterface;
use App\Support\PaginatedCollection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 * @OA\Schema(schema="GetMerchantPaymentsResponse", title="Merchant Payments Response", type="object", properties={
 *     @OA\Property(property="items", type="array", description="Merchant payment item", @OA\Items({
 *          @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="amount", ref="#/components/schemas/Money"),
 *          @OA\Property(property="transaction_date", ref="#/components/schemas/Date"),
 *          @OA\Property(property="is_allocated", type="boolean"),
 *          @OA\Property(property="transaction_counterparty_iban", ref="#/components/schemas/IBAN"),
 *          @OA\Property(property="transaction_counterparty_name", type="string"),
 *          @OA\Property(property="transaction_reference", type="string"),
 *          @OA\Property(property="merchant_debtor_uuid", ref="#/components/schemas/UUID"),
 *      })),
 *     @OA\Property(property="total", type="integer", description="Total number of results"),
 * })
 *
 * @OA\get(
 *     path="/payments",
 *     operationId="get_payments",
 *     summary="Get Payments",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Payments"},
 *     x={"groups":{"dashboard"}},
 *
 *     @OA\Parameter(in="query", name="sort_by", @OA\Schema(type="string", enum={"priority", "date"}, default="priority"), required=false),
 *     @OA\Parameter(in="query", name="sort_direction", @OA\Schema(type="string", enum={"desc", "asc"}, default="desc"), required=false),
 *     @OA\Parameter(in="query", name="offset", @OA\Schema(type="integer", minimum=0), required=false),
 *     @OA\Parameter(in="query", name="limit", @OA\Schema(type="integer", minimum=1, maximum=100, default=\App\Application\UseCase\PaginationAwareInterface::DEFAULT_LIMIT), required=false),
 *     @OA\Parameter(in="query", name="merchant_debtor_uuid", @OA\Schema(ref="#/components/schemas/UUID"), description="Merchant Debtor Uuid", required=false),
 *     @OA\Parameter(in="query", name="search", description="Search text.", @OA\Schema(ref="#/components/schemas/TinyText"), required=false),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetMerchantPaymentsResponse")
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantPaymentsController
{
    private $useCase;

    public function __construct(
        GetMerchantPaymentsUseCase $useCase
    ) {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): PaginatedCollection
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $data = $request->query->all();

        $useCaseRequest = (new GetMerchantPaymentsRequest())
            ->setMerchantId($merchantId)
            ->setMerchantDebtorUuid($data['merchant_debtor_uuid'] ?? null)
            ->setTransactionUuid($data['transaction_uuid'] ?? null)
            ->setSortBy($data['sort_by'] ?? GetMerchantPaymentsRequest::DEFAULT_SORTING_FIELD)
            ->setSortDirection($data['sort_direction'] ?? 'desc')
            ->setSearchKeyword($data['search'] ?? '')
            ->setLimit((int) ($data['limit'] ?? GetMerchantPaymentsRequest::DEFAULT_LIMIT))
            ->setOffset((int) ($data['offset'] ?? 0));

        try {
            $response = $this->useCase->execute($useCaseRequest);
        } catch (MerchantNotFoundException | MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $response;
    }
}
