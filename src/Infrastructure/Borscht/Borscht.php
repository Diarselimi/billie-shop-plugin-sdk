<?php

namespace App\Infrastructure\Borscht;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\Order\OrderEntity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Borscht implements BorschtInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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

        return $this->populateTicketResponseDTO($response);
    }

    public function cancelOrder(OrderEntity $order): void
    {
        try {
            $this->client->delete('/order.json', [
                'json' => [
                    'ticket_id' => $order->getPaymentId(),
                ],
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
        try {
            $this->client->put('/order.json', [
                'json' => [
                    'ticket_id' => $order->getPaymentId(),
                    'duration' => $order->getDuration(),
                    'amount' => $order->getAmountGross(),
                ],
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
        try {
            $this->client->put('/merchant/payment.json', [
                'json' => [
                    'ticket_id' => $order->getPaymentId(),
                    'amount' => $amount,
                ],
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

    public function ship(OrderEntity $order, string $debtorPaymentId): OrderPaymentDetailsDTO
    {
        try {
            $response = $this->client->post('/order.json', [
                'json' => [
                    'debtor_id' => $debtorPaymentId,
                    'invoice_number' => $order->getInvoiceNumber(),
                    'billing_date' => $order->getCreatedAt()->format('Y-m-d'),
                    'duration' => $order->getDuration(),
                    'amount' => $order->getAmountGross(),
                ],
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

        return $this->populateTicketResponseDTO($response);
    }

    private function populateTicketResponseDTO(array $response): OrderPaymentDetailsDTO
    {
        return (new OrderPaymentDetailsDTO())
            ->setId($response['id'])
            ->setPayoutAmount($response['payout_amount'])
            ->setOutstandingAmount($response['outstanding_amount'])
            ->setFeeAmount($response['fee_amount'])
            ->setFeeRate($response['fee_rate'])
            ->setDueDate(new \DateTime($response['due_date']))
        ;
    }
}
