<?php

namespace spec\App\Infrastructure\OrderNotification;

use App\DomainModel\OrderNotification\Exception\NotificationSenderException;
use App\DomainModel\OrderNotification\NotificationDeliveryResultDTO;
use App\Infrastructure\OrderNotification\NotificationSender;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class NotificationSenderSpec extends ObjectBehavior
{
    private const WEBHOOK_URL = 'https://test.merchant/webhook';

    private const DEFAULT_HEADERS = ['Content-Type' => 'application/json'];

    private const AUTHORISATION = 'X-Api-Key: PAYmlsbGllLm123X8rcWdmMkhlay5iTWc3LPQnz';

    private const PAYLOAD = ['test' => 'test'];

    public function let(Client $client, LoggerInterface $logger)
    {
        $this->beConstructedWith($client);

        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NotificationSender::class);
    }

    public function it_throws_notification_sender_exception_if_unexpected_exception_occurred(Client $client)
    {
        $client
            ->post(
                self::WEBHOOK_URL,
                ['json' => self::PAYLOAD, 'headers' => self::DEFAULT_HEADERS]
            )
            ->shouldBeCalled()
            ->willThrow(\Exception::class)
        ;

        $this->shouldThrow(NotificationSenderException::class)->during('send', [self::WEBHOOK_URL, null, self::PAYLOAD]);
    }

    public function it_returns_notification_delivery_result_dto_on_request_failure(Client $client)
    {
        $client
            ->post(
                self::WEBHOOK_URL,
                ['json' => self::PAYLOAD, 'headers' => self::DEFAULT_HEADERS]
            )
            ->shouldBeCalled()
            ->willReturn(new Response(500, [], 'Request Failed'))
        ;

        $notificationDeliveryResultDTO = $this->send(self::WEBHOOK_URL, null, self::PAYLOAD);

        $notificationDeliveryResultDTO->shouldHaveType(NotificationDeliveryResultDTO::class);
        $notificationDeliveryResultDTO->getResponseCode()->shouldBe(500);
        $notificationDeliveryResultDTO->getResponseBody()->shouldBe('Request Failed');
    }

    public function it_returns_notification_delivery_result_dto_on_request_success(Client $client)
    {
        $client
            ->post(
                self::WEBHOOK_URL,
                ['json' => self::PAYLOAD, 'headers' => self::DEFAULT_HEADERS]
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200))
        ;

        $notificationDeliveryResultDTO = $this->send(self::WEBHOOK_URL, null, self::PAYLOAD);

        $notificationDeliveryResultDTO->shouldHaveType(NotificationDeliveryResultDTO::class);
        $notificationDeliveryResultDTO->getResponseCode()->shouldBe(200);
    }

    public function it_includes_the_authorisation_header_in_request_if_specified(Client $client)
    {
        $headers = array_merge(self::DEFAULT_HEADERS, ['X-Api-Key' => 'PAYmlsbGllLm123X8rcWdmMkhlay5iTWc3LPQnz']);

        $client
            ->post(
                self::WEBHOOK_URL,
                ['json' => self::PAYLOAD, 'headers' => $headers]
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200))
        ;

        $notificationDeliveryResultDTO = $this->send(self::WEBHOOK_URL, self::AUTHORISATION, self::PAYLOAD);

        $notificationDeliveryResultDTO->shouldHaveType(NotificationDeliveryResultDTO::class);
        $notificationDeliveryResultDTO->getResponseCode()->shouldBe(200);
    }

    public function it_does_not_includes_the_authorisation_header_in_request_if_it_is_invalid(Client $client)
    {
        $client
            ->post(
                self::WEBHOOK_URL,
                ['json' => self::PAYLOAD, 'headers' => self::DEFAULT_HEADERS]
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200))
        ;

        $notificationDeliveryResultDTO = $this->send(self::WEBHOOK_URL, 'invalidHeader', self::PAYLOAD);

        $notificationDeliveryResultDTO->shouldHaveType(NotificationDeliveryResultDTO::class);
        $notificationDeliveryResultDTO->getResponseCode()->shouldBe(200);
    }
}
