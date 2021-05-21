<?php

namespace App\Infrastructure\Borscht;

use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\MerchantDebtor\RegisterDebtorDTO;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\Payment\DebtorPaymentRegistrationDTO;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Borscht implements PaymentsServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const ERR_BODY_DECODE_MESSAGE = 'Payments API response decode failed';

    private const EXTENDED_REQUEST_TIMEOUT = 5;

    private $client;

    public function __construct(Client $borschtClient)
    {
        $this->client = $borschtClient;
    }

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("debtor/{$debtorPaymentId}.json", ['retry_enabled' => false]);

            $decodedResponse = $this->decodeResponse($response);

            return (new DebtorPaymentDetailsDTO())
                ->setBankAccountBic($decodedResponse['bic'])
                ->setBankAccountIban($decodedResponse['iban'])
                ->setOutstandingAmount($decodedResponse['outstanding_amount']);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        } catch (ClientResponseDecodeException $exception) {
            throw new PaymentsServiceRequestException($exception, self::ERR_BODY_DECODE_MESSAGE);
        }
    }

    public function cancelOrder(OrderEntity $order): void
    {
        $json = ['ticket_id' => $order->getPaymentId()];

        $this->logInfo('Cancel borscht ticket', [
            LoggingInterface::KEY_SOBAKA => $json,
        ]);

        try {
            $this->client->delete('order.json', [
                'json' => $json,
                'retry_enabled' => false,
            ]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function modifyOrder(ModifyRequestDTO $requestDTO): void
    {
        $this->logInfo('Modify borscht ticket', [
            LoggingInterface::KEY_SOBAKA => [
                'request' => $requestDTO->toArray(),
                'timeout' => self::EXTENDED_REQUEST_TIMEOUT,
            ],
        ]);

        try {
            $this->client->put('order.json', ['json' => $requestDTO->toArray(), 'retry_enabled' => false]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function confirmPayment(ConfirmRequestDTO $requestDTO): void
    {
        $this->logInfo('Confirm borscht ticket payment', [
            LoggingInterface::KEY_SOBAKA => $requestDTO->toArray(),
        ]);

        try {
            $this->client->post('merchant/payment.json', [
                'json' => $requestDTO->toArray(),
                'retry_enabled' => false,
            ]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function registerDebtor(RegisterDebtorDTO $registerDebtorDTO): DebtorPaymentRegistrationDTO
    {
        $json = ['company_uuid' => $registerDebtorDTO->getCompanyUuid()];

        try {
            $response = $this->client->post('debtor.json', [
                'headers' => [
                    'x-merchant-id' => $registerDebtorDTO->getMerchantPaymentUuid(),
                ],
                'json' => $json,
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'create_borscht_debtor');
                },
                'retry_enabled' => false,
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return new DebtorPaymentRegistrationDTO($decodedResponse['debtor_id']);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        } catch (ClientResponseDecodeException $exception) {
            throw new PaymentsServiceRequestException($exception, self::ERR_BODY_DECODE_MESSAGE);
        }
    }

    public function createFraudReclaim(string $orderPaymentId): void
    {
        try {
            $this->client->post("order/$orderPaymentId/fraud-reclaim.json", ['retry_enabled' => false]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception, 'Fraud reclaim request to Payment failed');
        }
    }

    public function extendInvoiceDuration(Invoice $invoice, Duration $duration): void
    {
        $request = (new ModifyRequestDTO())
            ->setInvoiceNumber($invoice->getExternalCode())
            ->setPaymentUuid($invoice->getPaymentUuid())
            ->setAmountGross($invoice->getGrossAmount()->getMoneyValue())
            ->setDuration($duration->days());
        $this->modifyOrder($request);
    }
}
