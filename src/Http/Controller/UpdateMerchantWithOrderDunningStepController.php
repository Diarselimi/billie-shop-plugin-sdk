<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateMerchantWithOrderDunningStepController
{
    private $useCase;

    public function __construct(UpdateMerchantWithOrderDunningStepUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $uuid): void
    {
        try {
            $useCaseRequest = new UpdateMerchantWithOrderDunningStepRequest($uuid, $request->request->get('step'));
            $this->useCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
