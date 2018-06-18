<?php

namespace App\Infrastructure\Borscht;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Borscht\OrderPaymentDetailsFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Borscht implements BorschtInterface, LoggingInterface
{
    use LoggingTrait;

    private $client;
    private $paymentDetailsFactory;

    public function __construct(Client $client, OrderPaymentDetailsFactory $paymentDetailsFactory)
    {
        $this->client = $client;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
    }

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("/debtor/$debtorPaymentId.json");
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Borscht is not available right now',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $response = (string)$response->getBody();
        $response = json_decode($response, true);
        if (!$response) {
            throw new PaellaCoreCriticalException(
                'Borscht response decode exception',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION
            );
        }

        return (new DebtorPaymentDetailsDTO())
            ->setBankAccountBic($response['bic'])
            ->setBankAccountIban($response['iban']);
    }

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("/order/$orderPaymentId.json");
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Borscht is not available right now',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $response = (string)$response->getBody();
        $response = json_decode($response, true);

        if (!$response) {
            throw new PaellaCoreCriticalException(
                'Borscht response decode exception',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION
            );
        }

        return $this->paymentDetailsFactory->createFromBorschtResponse($response);
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
                'Borscht is not available right now',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    public function modifyOrder(OrderEntity $order): void
    {
        $json = [
            'ticket_id' => $order->getPaymentId(),
            'invoice_number' => $order->getInvoiceNumber(),
            'duration' => $order->getDuration(),
            'amount' => $order->getAmountGross(),
        ];

        $this->logInfo('Modify borscht ticket', [
            'json' => $json,
        ]);

        try {
            $this->client->put('/order.json', [
                'json' => $json,
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Borscht is not available right now',
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
            $this->client->put('/merchant/payment.json', [
                'json' => $json,
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Borscht is not available right now',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }
    }

    public function createOrder(OrderEntity $order, string $debtorPaymentId): OrderPaymentDetailsDTO
    {
        $json = [
            'debtor_id' => $debtorPaymentId,
            'invoice_number' => $order->getInvoiceNumber(),
            'billing_date' => $order->getCreatedAt()->format('Y-m-d'),
            'duration' => $order->getDuration(),
            'amount' => $order->getAmountGross(),
        ];

        $this->logInfo('Create borscht ticket', [
            'json' => $json,
        ]);

        try {
            $response = $this->client->post('/order.json', [
                'json' => $json,
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Borscht is not available right now',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION,
                null,
                $exception
            );
        }

        $response = (string)$response->getBody();
        $response = json_decode($response, true);

        if (!$response) {
            throw new PaellaCoreCriticalException(
                'Borscht response decode exception',
                PaellaCoreCriticalException::CODE_BORSCHT_EXCEPTION
            );
        }

        return $this->paymentDetailsFactory->createFromBorschtResponse($response);
    }
}
