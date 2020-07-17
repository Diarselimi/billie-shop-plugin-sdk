<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantDebtorExternalIds;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\Infrastructure\Repository\DebtorExternalDataRepository;

class GetMerchantDebtorExternalIdsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $debtorExternalDataRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        DebtorExternalDataRepository $debtorExternalDataRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
    }

    public function execute(GetMerchantDebtorExternalIdsRequest $request): array
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepository->getOneByUuidAndMerchantId(
            $request->getMerchantDebtorUuid(),
            $request->getMerchantId()
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        return $this->debtorExternalDataRepository->getMerchantDebtorExternalIds($merchantDebtor->getId());
    }
}
