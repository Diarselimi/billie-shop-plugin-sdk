<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrders\GetOrdersRequest;
use App\Application\UseCase\GetOrders\GetOrdersUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetOrdersController
{
    private $useCase;

    public function __construct(GetOrdersUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        [$sortField, $sortDirection] = ($request->query->has('sort_by')) ?
            explode(',', $request->query->get('sort_by')) :
            [GetOrdersRequest::DEFAULT_SORT_FIELD, GetOrdersRequest::DEFAULT_SORT_DIRECTION]
        ;

        $useCaseRequest = new GetOrdersRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetOrdersRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection),
            $request->query->get('search'),
            $request->query->get('filters')
        );

        $useCaseResponse = $this->useCase->execute($useCaseRequest);

        return new JsonResponse($useCaseResponse->toArray());
    }
}
