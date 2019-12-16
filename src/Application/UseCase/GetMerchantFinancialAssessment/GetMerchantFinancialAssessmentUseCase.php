<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentRepositoryInterface;

class GetMerchantFinancialAssessmentUseCase
{
    private $repository;

    public function __construct(MerchantFinancialAssessmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(GetMerchantFinancialAssessmentRequest $request): GetMerchantFinancialAssessmentResponse
    {
        $financialAssessment = $this->repository->findOneByMerchant($request->getMerchantId());

        if (!$financialAssessment) {
            throw new FinancialAssessmentNotFoundException();
        }

        return new GetMerchantFinancialAssessmentResponse($financialAssessment);
    }
}
