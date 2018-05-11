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

    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    public function getDebtorPaymentDetails(int $debtorPaymentId): DebtorPaymentDetailsDTO
    {
        return (new DebtorPaymentDetailsDTO())
            ->setBankAccountBic('BICDEXXX')
            ->setBankAccountIban('DE112233');
    }

    public function getOrderPaymentDetails(int $orderPaymentId): OrderPaymentDetailsDTO
    {
        return (new OrderPaymentDetailsDTO())
            ->setPayoutAmount(5000)
            ->setFeeAmount(100)
            ->setFeeRate(1.5)
            ->setDueDate(new \DateTime());
    }

    public function cancelOrder(OrderEntity $order): void
    {
        try {
            $this->client->delete('/order.json', [
                'json' => [
                    'order_id' => $order->getId(),
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
                    'order_id' => $order->getId(),
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
}
