<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use App\DomainModel\Payment\OrderAmountChangeFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OrderOutstandingAmountChangeConsumer implements ConsumerInterface
{
    private $useCase;

    private $orderAmountChangeFactory;

    public function __construct(
        OrderOutstandingAmountChangeUseCase $useCase,
        OrderAmountChangeFactory $orderAmountChangeFactory
    ) {
        $this->useCase = $useCase;
        $this->orderAmountChangeFactory = $orderAmountChangeFactory;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $paymentDetails = $this->orderAmountChangeFactory->createFromBorschtResponse($data);

        $request = new OrderOutstandingAmountChangeRequest($paymentDetails);
        $this->useCase->execute($request);
    }
}
