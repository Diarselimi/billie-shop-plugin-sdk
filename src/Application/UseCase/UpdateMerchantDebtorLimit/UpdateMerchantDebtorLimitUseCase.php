<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantDebtorLimitUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorFinancialDetailsRepository;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        BorschtInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
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

        $merchantDebtorFinancingDetails = $this->merchantDebtorFinancialDetailsRepository->getCurrentByMerchantDebtorId(
            $merchantDebtor->getId()
        );

        $paymentsDebtor = $this->paymentsService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());
        $createdAmount = $this->merchantDebtorRepository->getMerchantDebtorCreatedOrdersAmount($merchantDebtor->getId());
        $newFinancingPower = $request->getLimit() - $paymentsDebtor->getOutstandingAmount() - $createdAmount;

        $this->logInfo('Merchant debtor {external_id} (id:{id}) limit updated to {new_limit}', [
            'external_id' => $request->getMerchantDebtorExternalId(),
            'merchant_id' => $request->getMerchantId(),
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'old_limit' => $merchantDebtorFinancingDetails->getFinancingLimit(),
            'new_limit' => $request->getLimit(),
            'outstanding_amount' => $paymentsDebtor->getOutstandingAmount(),
            'created_amount' => $createdAmount,
        ]);

        $merchantDebtorFinancingDetails
            ->setFinancingLimit($request->getLimit())
            ->setFinancingPower($newFinancingPower)
        ;

        $this->merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancingDetails);
    }
}
