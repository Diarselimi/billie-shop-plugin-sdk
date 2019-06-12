<?php

namespace App\Infrastructure\Borscht;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\Borscht\DebtorPaymentRegistrationDTO;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\Borscht\OrderPaymentDetailsFactory;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class Borscht implements BorschtInterface, LoggingInterface
{
    use LoggingTrait;

    private const ERR_DEFAULT_MESSAGE = 'Payments API call was not successful';

    private const ERR_BODY_DECODE_MESSAGE = 'Borscht response decode exception';

    private $client;

    private $paymentDetailsFactory;

    public function __construct(Client $borschtClient, OrderPaymentDetailsFactory $paymentDetailsFactory)
    {
        $this->client = $borschtClient;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
    }

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("/debtor/$debtorPaymentId.json");
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $decodedResponse = $this->decodeResponse($response);

        return (new DebtorPaymentDetailsDTO())
            ->setBankAccountBic($decodedResponse['bic'])
            ->setBankAccountIban($decodedResponse['iban'])
            ->setOutstandingAmount($decodedResponse['outstanding_amount'])
        ;
    }

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("/order/$orderPaymentId.json");
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->paymentDetailsFactory->createFromBorschtResponse($decodedResponse);
    }

    public function cancelOrder(OrderEntity $order): void
    {
        $json = ['ticket_id' => $order->getPaymentId()];

        $this->logInfo('Cancel borscht ticket', [
            'json' => $json,
        ]);

        try {
            $this->client->delete('/order.json', [
                'json' => $json,
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    public function modifyOrder(string $paymentId, int $duration, float $amountGross, ?string $invoiceNumber): void
    {
        $json = [
            'ticket_id' => $paymentId,
            'invoice_number' => $invoiceNumber,
            'duration' => $duration,
            'amount' => $amountGross,
        ];

        $this->logInfo('Modify borscht ticket', [
            'json' => $json,
        ]);

        try {
            $this->client->put('/order.json', ['json' => $json]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    public function confirmPayment(OrderEntity $order, float $amount): void
    {
        $json = [
            'ticket_id' => $order->getPaymentId(),
            'amount' => $amount,
        ];

        $this->logInfo('Confirm borscht ticket payment', [
            'json' => $json,
        ]);

        try {
            $this->client->post('/merchant/payment.json', [
                'json' => $json,
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    public function createOrder(
        string $debtorPaymentId,
        string $invoiceNumber,
        \DateTime $shippedAt,
        int $duration,
        float $amountGross,
        string $externalCode
    ): OrderPaymentDetailsDTO {
        $json = [
            'debtor_id' => $debtorPaymentId,
            'invoice_number' => $invoiceNumber,
            'billing_date' => $shippedAt->format('Y-m-d'),
            'duration' => $duration,
            'amount' => $amountGross,
            'order_code' => $externalCode,
        ];

        $this->logInfo('Create borscht ticket', ['json' => $json]);

        try {
            $response = $this->client->post('/order.json', [
                'json' => $json,
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'create_borscht_ticket');
                },
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->paymentDetailsFactory->createFromBorschtResponse($decodedResponse);
    }

    public function registerDebtor(string $paymentMerchantId): DebtorPaymentRegistrationDTO
    {
        try {
            $response = $this->client->post('debtor.json', [
                'headers' => [
                    'x-merchant-id' => $paymentMerchantId,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'create_borscht_debtor');
                },
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                self::ERR_DEFAULT_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $decodedResponse = $this->decodeResponse($response);

        return new DebtorPaymentRegistrationDTO($decodedResponse['debtor_id']);
    }

    public function createFraudReclaim(string $orderPaymentId): void
    {
        try {
            $this->client->post("/order/$orderPaymentId/fraud-reclaim.json");
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Fraud reclaim request to Borscht failed',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $response = (string) $response->getBody();
        $decodedResponse = json_decode($response, true);

        if (!$decodedResponse) {
            throw new PaellaCoreCriticalException(
                self::ERR_BODY_DECODE_MESSAGE,
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION
            );
        }

        return $decodedResponse;
    }
}
