<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\Application\UseCase\CreateMerchant\Exception\CreateMerchantException;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CreateMerchantController
{
    private $useCase;

    public function __construct(CreateMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        try {
            $request = (new CreateMerchantRequest())
                ->setCompanyId($request->request->get('company_id'))
                ->setMerchantFinancingLimit($request->request->get('merchant_financing_limit'))
                ->setInitialDebtorFinancingLimit($request->request->get('initial_debtor_financing_limit'))
                ->setDebtorFinancingLimit($request->request->get('debtor_financing_limit'))
                ->setWebhookUrl($request->request->get('webhook_url'))
                ->setWebhookAuthorization($request->request->get('webhook_authorization'))
            ;

            $response = $this->useCase->execute($request);

            return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
        } catch (DuplicateMerchantCompanyException $e) {
            throw new ConflictHttpException($e->getMessage());
        } catch (MerchantCompanyNotFoundException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (CreateMerchantException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
}
