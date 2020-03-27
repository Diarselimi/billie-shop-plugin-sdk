<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;

class GetMerchantDebtorUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    private $debtorInformationChangeRequestRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        DebtorInformationChangeRequestRepositoryInterface $debtorInformationChangeRequestRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
        $this->debtorInformationChangeRequestRepository = $debtorInformationChangeRequestRepository;
    }

    public function execute(GetMerchantDebtorRequest $request): MerchantDebtorContainer
    {
        $this->validateRequest($request);

        $merchantDebtor = $request->getMerchantId() ?
            $this->merchantDebtorRepository->getOneByUuidAndMerchantId(
                $request->getMerchantDebtorUuid(),
                $request->getMerchantId()
            ) : $this->merchantDebtorRepository->getOneByUuid(
                $request->getMerchantDebtorUuid()
            );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $merchantDebtorContainer = $this->merchantDebtorContainerFactory->create($merchantDebtor);
        $debtorInformationChangeRequest = $merchantDebtorContainer->getDebtorInformationChangeRequest();
        if ($debtorInformationChangeRequest && $debtorInformationChangeRequest->getState() !== DebtorInformationChangeRequestEntity::STATE_PENDING) {
            $debtorInformationChangeRequest->setIsSeen(true);
            $this->debtorInformationChangeRequestRepository->update($debtorInformationChangeRequest);
        }

        return $merchantDebtorContainer;
    }
}
