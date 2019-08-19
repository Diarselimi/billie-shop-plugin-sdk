<?php

namespace App\Infrastructure\Borscht;

use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\Payment\DebtorPaymentRegistrationDTO;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\OrderPaymentDetailsFactory;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\CreateRequestDTO;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class Borscht implements PaymentsServiceInterface, LoggingInterface
{
    use LoggingTrait;

    private const ERR_DEFAULT_MESSAGE = 'Payments API call was not successful';

    private const ERR_BODY_DECODE_MESSAGE = 'Payments API response decode failed';

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
            throw new PaymentsServiceRequestException($exception);
        }

        $decodedResponse = $this->decodeResponse($response);

        return (new DebtorPaymentDetailsDTO())
            ->setBankAccountBic($decodedResponse['bic'])
            ->setBankAccountIban($decodedResponse['iban'])
            ->setOutstandingAmount($decodedResponse['outstanding_amount']);
    }

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO
    {
        try {
            $response = $this->client->get("/order/$orderPaymentId.json");
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
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
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function modifyOrder(ModifyRequestDTO $requestDTO): void
    {
        $this->logInfo('Modify borscht ticket', [
            'json' => $requestDTO->toArray(),
        ]);

        try {
            $this->client->put('/order.json', ['json' => $requestDTO->toArray()]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function confirmPayment(ConfirmRequestDTO $requestDTO): void
    {
        $this->logInfo('Confirm borscht ticket payment', [
            'json' => $requestDTO->toArray(),
        ]);

        try {
            $this->client->post('/merchant/payment.json', [
                'json' => $requestDTO->toArray(),
            ]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
        }
    }

    public function createOrder(CreateRequestDTO $requestDTO): OrderPaymentDetailsDTO
    {
        $json = $requestDTO->toArray();

        $this->logInfo('Create borscht ticket', ['json' => $json]);

        try {
            $response = $this->client->post('/order.json', [
                'json' => $json,
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'create_borscht_ticket');
                },
            ]);
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception);
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
            throw new PaymentsServiceRequestException($exception);
        }

        $decodedResponse = $this->decodeResponse($response);

        return new DebtorPaymentRegistrationDTO($decodedResponse['debtor_id']);
    }

    public function createFraudReclaim(string $orderPaymentId): void
    {
        try {
            $this->client->post("/order/$orderPaymentId/fraud-reclaim.json");
        } catch (TransferException $exception) {
            throw new PaymentsServiceRequestException($exception, 'Fraud reclaim request to Payment failed');
        }
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $response = (string) $response->getBody();
        $decodedResponse = json_decode($response, true);

        if (!$decodedResponse) {
            throw new PaymentsServiceRequestException(null, self::ERR_BODY_DECODE_MESSAGE);
        }

        return $decodedResponse;
    }
}
