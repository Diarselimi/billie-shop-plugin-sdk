<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\OrderPaymentStateChange\OrderPaymentStateChangeRequest;
use App\Application\UseCase\OrderPaymentStateChange\OrderPaymentStateChangeUseCase;
use App\DomainModel\Borscht\OrderPaymentDetailsFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Exception\AckStopConsumerException;
use PhpAmqpLib\Message\AMQPMessage;

class OrderPaymentStateChangeConsumer implements ConsumerInterface
{
    private $useCase;
    private $paymentDetailsFactory;

    public function __construct(
        OrderPaymentStateChangeUseCase $useCase,
        OrderPaymentDetailsFactory $paymentDetailsFactory
    ) {
        $this->useCase = $useCase;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $paymentDetails = $this->paymentDetailsFactory->createFromBorschtResponse($data);

        $request = new OrderPaymentStateChangeRequest($paymentDetails);
        $this->useCase->execute($request);

        throw new AckStopConsumerException();
    }
}
