<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantDebtorLimitUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->paymentsService = $paymentsService;
    }

    public function execute(UpdateMerchantDebtorLimitRequest $request): void
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId(),
            []
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $paymentsDebtor = $this->paymentsService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());
        $newAvailableLimit = $request->getLimit() - $paymentsDebtor->getOutstandingAmount();

        $this->logInfo('Merchant debtor {external_id} (id:{id}) limit updated to {new_limit}', [
            'external_id' => $request->getMerchantDebtorExternalId(),
            'merchant_id' => $request->getMerchantId(),
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'old_limit' => $merchantDebtor->getFinancingLimit(),
            'new_limit' => $newAvailableLimit,
            'outstanding_amount' => $paymentsDebtor->getOutstandingAmount(),
        ]);

        $merchantDebtor->setFinancingLimit($newAvailableLimit);
        $this->merchantDebtorRepository->update($merchantDebtor);
    }
}
