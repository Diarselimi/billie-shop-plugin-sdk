<?php

namespace App\Infrastructure\OrderNotification;

use App\DomainModel\OrderNotification\Exception\NotificationSenderException;
use App\DomainModel\OrderNotification\NotificationDeliveryResultDTO;
use App\DomainModel\OrderNotification\NotificationSenderInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NotificationSender implements NotificationSenderInterface, LoggingInterface
{
    use LoggingTrait;

    private Client $client;

    public function __construct(Client $notificationSenderClient)
    {
        $this->client = $notificationSenderClient;
    }

    public function send(string $url, ?string $authorisation, array $data): NotificationDeliveryResultDTO
    {
        $this->logInfo('Webhook request', [
            LoggingInterface::KEY_URL => $url,
            LoggingInterface::KEY_SOBAKA => $data,
        ]);

        $headers = ['Content-Type' => 'application/json'];

        if ($authorisation && strpos($authorisation, ':') !== false) {
            $authorisationHeaderNameValue = explode(':', $authorisation);

            $headers[$authorisationHeaderNameValue[0]] = trim($authorisationHeaderNameValue[1]);
        }

        try {
            $response = $this->client->post($url, [
                'json' => $data,
                'headers' => $headers,
            ]);
        } catch (RequestException $exception) {
            $this->logError('Exception while delivering notification', [
                LoggingInterface::KEY_EXCEPTION => $exception,
            ]);

            if ($exception->getResponse() !== null) {
                return new NotificationDeliveryResultDTO(
                    $exception->getResponse()->getStatusCode(),
                    (string) $exception->getResponse()->getBody()
                );
            }

            return new NotificationDeliveryResultDTO(0, 'Connection exception');
        } catch (\Exception $exception) {
            throw new NotificationSenderException('Unhandled exception while delivering notification', null, $exception);
        }

        return new NotificationDeliveryResultDTO($response->getStatusCode(), (string) $response->getBody() ?: null);
    }
}
