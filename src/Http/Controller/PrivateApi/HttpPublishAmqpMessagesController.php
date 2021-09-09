<?php

declare(strict_types=1);

namespace App\Http\Controller\PrivateApi;

use Billie\MonitoringBundle\Service\RidProvider;
use Enqueue\MessengerAdapter\EnvelopeItem\TransportConfiguration;
use Google\Protobuf\Internal\Message;
use Ozean12\AmqpPackBundle\Transport\RequestIdStamp;
use Ozean12\AmqpTransfers\Mapping\Amqp\AmqpMapperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/publish-to-amqp",
 *     operationId="publish_to_amqp",
 *     summary="Publish a message to the amqp broker",
 *
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="class_name", type="string", description="What message to publish"),
 *              @OA\Property(property="json", type="object", description="Message's body")
 *          }))
 *     ),
 *
 *     @OA\Response(response=202, description="Successful response"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class HttpPublishAmqpMessagesController
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var AmqpMapperInterface
     */
    private $amqpMapper;

    /**
     * @var RidProvider
     */
    private $requestIdProvider;

    public function __construct(
        MessageBusInterface $messageBus,
        AmqpMapperInterface $amqpMapper,
        RidProvider $requestIdProvider
    ) {
        $this->messageBus = $messageBus;
        $this->amqpMapper = $amqpMapper;
        $this->requestIdProvider = $requestIdProvider;
    }

    public function execute(Request $request)
    {
        $className = $request->get('class_name');
        $json = $request->get('json');

        if (empty($className) or empty($json)) {
            throw new BadRequestHttpException('Either "class_name" is empty or "json" in the POST request');
        }

        $className = $this->amqpMapper->mapToClassName($className);

        /**
         * @var Message $message
         */
        $message = new $className;
        $message->mergeFromJsonString(\GuzzleHttp\json_encode($json));
        $this->produceMessage($message);

        return new Response('', Response::HTTP_ACCEPTED);
    }

    public function produceMessage($message)
    {
        $this->messageBus->dispatch(
            (new Envelope($message))
                ->with(new RequestIdStamp($this->requestIdProvider->getRid()))
                ->with(
                    new TransportConfiguration(
                        [
                            'topic' => 'front',
                            'metadata' => [
                                'routingKey' => $this->amqpMapper->mapToKey(get_class($message)),
                            ],
                        ]
                    )
                )
        );
    }
}
