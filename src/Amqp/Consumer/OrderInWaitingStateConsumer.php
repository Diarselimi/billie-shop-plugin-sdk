<?php

namespace App\Amqp\Consumer;

use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OrderInWaitingStateConsumer implements ConsumerInterface, LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(DeclineOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new DeclineOrderRequest($data['order_id']);

        try {
            $this->useCase->execute($request);
        } catch (OrderWorkflowException $exception) {
            return; // order was already manually approved/declined
        } catch (\Exception $exception) {
            $this->logSuppressedException(
                $exception,
                "Failed to move the order to declined because of {reason}",
                ['reason' => $exception->getMessage()]
            );
        }
    }
}
