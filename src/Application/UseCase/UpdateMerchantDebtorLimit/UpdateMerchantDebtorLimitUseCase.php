<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Payment\PaymentsServiceInterface;
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
        PaymentsServiceInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->paymentsService = $paymentsService;
    }

    public function execute(UpdateMerchantDebtorLimitRequest $request): void
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepository->getOneByUuid($request->getMerchantDebtorUuid());

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $merchantDebtorFinancingDetails = $this->merchantDebtorFinancialDetailsRepository->getCurrentByMerchantDebtorId(
            $merchantDebtor->getId()
        );

        $paymentsDebtor = $this->paymentsService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());
        $createdAmount = $this->merchantDebtorRepository->getMerchantDebtorCreatedOrdersAmount($merchantDebtor->getId());
        $newFinancingPower = $request->getLimit() - $paymentsDebtor->getOutstandingAmount() - $createdAmount;

        $merchantDebtorFinancingDetails
            ->setFinancingLimit($request->getLimit())
            ->setFinancingPower($newFinancingPower);

        $this->merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancingDetails);

        $this->logInfo('Merchant debtor {external_id} (id:{id}) limit updated to {new_limit}', [
            'uuid_or_external_id' => $request->getMerchantDebtorUuid(),
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'old_limit' => $merchantDebtorFinancingDetails->getFinancingLimit(),
            'new_limit' => $request->getLimit(),
            'outstanding_amount' => $paymentsDebtor->getOutstandingAmount(),
            'created_amount' => $createdAmount,
        ]);
    }
}
