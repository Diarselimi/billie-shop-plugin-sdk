<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OrderDunningStepChangedConsumer implements ConsumerInterface
{
    private $useCase;

    public function __construct(UpdateMerchantWithOrderDunningStepUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        if (!isset($data['uuid']) || !isset($data['dunning_step'])) {
            return;
        }

        $this->useCase->execute(new UpdateMerchantWithOrderDunningStepRequest($data['uuid'], $data['dunning_step']));
    }
}
