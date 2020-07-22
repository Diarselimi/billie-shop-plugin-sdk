<?php

namespace App\Application\UseCase\VerifyNewDebtorExternalCode;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;

class VerifyNewDebtorExternalCodeUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $debtorExternalDataRepo;

    public function __construct(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepo
    ) {
        $this->debtorExternalDataRepo = $debtorExternalDataRepo;
    }

    public function execute(VerifyNewDebtorExternalCodeRequest $request): void
    {
        $this->validateRequest($request);

        $debtorExternalData = $this->debtorExternalDataRepo->getOneByMerchantIdAndExternalCode(
            $request->getMerchantId(),
            $request->getExternalCode()
        );

        if ($debtorExternalData) {
            throw new DebtorExternalCodeTakenException();
        }
    }
}
