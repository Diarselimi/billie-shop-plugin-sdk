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

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send(string $url, ?string $authorisation, array $data): NotificationDeliveryResultDTO
    {
        $this->logInfo('Webhook request', [
            'url' => $url,
            'request' => $data,
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
            $this->logSuppressedException($exception, 'Exception while delivering notification');
            $response = $exception->getResponse();

            return new NotificationDeliveryResultDTO($response->getStatusCode(), (string) $response->getBody() ?: null);
        } catch (\Exception $exception) {
            throw new NotificationSenderException('Unhandled exception while delivering notification', null, $exception);
        }

        return new NotificationDeliveryResultDTO($response->getStatusCode(), (string) $response->getBody() ?: null);
    }
}
