<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsRequest;
use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class GetMerchantDebtorsController
{
    private $useCase;

    public function __construct(GetMerchantDebtorsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): MerchantDebtorList
    {
        [$sortField, $sortDirection] = ($request->query->has('sort_by')) ?
            explode(',', $request->query->get('sort_by')) :
            [GetMerchantDebtorsRequest::DEFAULT_SORT_FIELD, GetMerchantDebtorsRequest::DEFAULT_SORT_DIRECTION];

        $useCaseRequest = new GetMerchantDebtorsRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $request->query->getInt('offset', 0),
            $request->query->getInt('limit', GetMerchantDebtorsRequest::DEFAULT_LIMIT),
            $sortField,
            strtoupper($sortDirection),
            $request->query->get('search')
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
