<?php

namespace App\Application\UseCase\UpdateRiskCheckResult;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;

class UpdateRiskCheckResultUseCase
{
    private $riskCheckRepository;

    public function __construct(RiskCheckRepositoryInterface $riskCheckRepository)
    {
        $this->riskCheckRepository = $riskCheckRepository;
    }

    public function execute(UpdateRiskCheckResultRequest $request)
    {
        $riskCheck = $this->riskCheckRepository->getOneById($request->getId());

        if (!$riskCheck) {
            throw new PaellaCoreCriticalException('Risk check not found', PaellaCoreCriticalException::CODE_NOT_FOUND);
        }

        $riskCheck->setCheckId($request->getRiskCheckId());
        $this->riskCheckRepository->update($riskCheck);
    }
}
