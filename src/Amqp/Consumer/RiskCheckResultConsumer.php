<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\UpdateRiskCheckResult\UpdateRiskCheckResultRequest;
use App\Application\UseCase\UpdateRiskCheckResult\UpdateRiskCheckResultUseCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RiskCheckResultConsumer implements ConsumerInterface
{
    private $useCase;

    public function __construct(UpdateRiskCheckResultUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new UpdateRiskCheckResultRequest($data['event_id'], $data['id']);
        $this->useCase->execute($request);

        // dead loop prevention is disabled
//        throw new AckStopConsumerException();
    }
}
