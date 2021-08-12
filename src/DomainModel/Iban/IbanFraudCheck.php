<?php

namespace App\DomainModel\Iban;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Support\HttpClient\Exception\HttpExceptionInterface;
use Ozean12\Support\ValueObject\Iban;
use Ozean12\Watson\Client\DomainModel\WatsonClientInterface;

class IbanFraudCheck implements LoggingInterface
{
    use LoggingTrait;

    public const RISK_CHECK_NAME = 'iban_fraud';

    private WatsonClientInterface $watsonClient;

    private OrderRiskCheckRepositoryInterface $orderRiskCheckRepository;

    private OrderRiskCheckEntityFactory $orderRiskCheckEntityFactory;

    public function __construct(WatsonClientInterface $watsonClient, OrderRiskCheckRepositoryInterface $orderRiskCheckRepository, OrderRiskCheckEntityFactory $orderRiskCheckEntityFactory)
    {
        $this->watsonClient = $watsonClient;
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->orderRiskCheckEntityFactory = $orderRiskCheckEntityFactory;
    }

    public function check(Iban $iban, OrderEntity $order): bool
    {
        try {
            $isFraud = $this->watsonClient->checkIbanFraud($iban)->isFraud();
        } catch (HttpExceptionInterface $exception) {
            $this->logSuppressedException($exception, 'IBAN Fraud check failed');
            $isFraud = false;
        }

        $checkResult = new CheckResult(!$isFraud, self::RISK_CHECK_NAME);
        $this->orderRiskCheckRepository->insert(
            $this->orderRiskCheckEntityFactory->createFromCheckResult($checkResult, $order->getId())
        );

        return !$isFraud;
    }
}
