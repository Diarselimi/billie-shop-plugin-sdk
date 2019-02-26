<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use Symfony\Component\HttpFoundation\Request;

class CreateMerchantController
{
    private $useCase;

    public function __construct(CreateMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): CreateMerchantResponse
    {
        $request = (new CreateMerchantRequest())
            ->setCompanyId($request->request->get('company_id'))
            ->setMerchantFinancingLimit($request->request->get('merchant_financing_limit'))
            ->setDebtorFinancingLimit($request->request->get('debtor_financing_limit'))
            ->setWebhookUrl($request->request->get('webhook_url'))
            ->setWebhookAuthorization($request->request->get('webhook_authorization'))
        ;

        $response = $this->useCase->execute($request);

        return $response;
    }
}
