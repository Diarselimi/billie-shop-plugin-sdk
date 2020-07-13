<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Fraud\FraudRequestDTOFactory;
use App\DomainModel\Fraud\FraudServiceException;
use App\DomainModel\Fraud\FraudServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\Http\IpAddressProvider;

class FraudScoreCheck implements CheckInterface
{
    public const NAME = 'fraud_score';

    private $fraudService;

    private $requestDTOFactory;

    private $ipAddress;

    public function __construct(
        FraudServiceInterface $fraudService,
        FraudRequestDTOFactory $requestDTOFactory,
        IpAddressProvider $ipAddressProvider
    ) {
        $this->fraudService = $fraudService;
        $this->requestDTOFactory = $requestDTOFactory;
        $this->ipAddress = $ipAddressProvider->getIpAddress();
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        try {
            $fraudResponse = $this->fraudService->check(
                $this->requestDTOFactory->createFromOrderContainer($orderContainer, $this->ipAddress)
            );
            $isFraud = $fraudResponse->isFraud();
        } catch (FraudServiceException $exception) {
            $isFraud = false;
        }

        return new CheckResult(!$isFraud, self::NAME);
    }
}
