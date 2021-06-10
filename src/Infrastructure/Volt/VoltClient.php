<?php

namespace App\Infrastructure\Volt;

use App\DomainModel\Fee\Fee;
use App\DomainModel\Fee\FeeCalculatorInterface;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;

class VoltClient implements FeeCalculatorInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const DEFAULT_DISCOUNT_RATE = 0;

    private Client $client;

    private VoltResponseFactory $responseFactory;

    public function __construct(Client $voltClient, VoltResponseFactory $responseFactory)
    {
        $this->client = $voltClient;
        $this->responseFactory = $responseFactory;
    }

    public function getCalculateFee(?string $ticketUuid, Money $amount, \DateTime $billingDate, \DateTime $dueDate, array $feeRates): Fee
    {
        $payload = [
            'invoice' => [
                'payment_uuid' => $ticketUuid,
                'approved_date' => $billingDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d'),
                'gross_amount' => $amount->shift(2)->toInt(),
            ],
            'fee' => [
                'discount_rate' => self::DEFAULT_DISCOUNT_RATE,
                'fee_rates' => array_map(fn (Percent $rate) => $rate->shift(2)->toInt(), $feeRates),
            ],
        ];

        $this->logInfo('Requesting the fee from Volt', [
            LoggingInterface::KEY_SOBAKA => $payload,
        ]);

        try {
            $response = $this->client->get(
                'api/v1/fees/factoring/calculate',
                [
                    'json' => $payload,
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'volt_calculate_fee');
                    },
                ]
            );

            return $this->responseFactory->createFeeFromResponse($this->decodeResponse($response));
        } catch (ClientException | TransferException $exception) {
            throw new VoltServiceException($exception);
        }
    }
}
