<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2Request;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2UseCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OrderDebtorIdentificationV2Consumer implements ConsumerInterface
{
    private $useCase;

    public function __construct(OrderDebtorIdentificationV2UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new OrderDebtorIdentificationV2Request($data['order_id'], $data['v1_company_id']);
        $this->useCase->execute($request);
    }
}
