<?php

namespace App\Amqp\Consumer;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractOrderDeclineByStateConsumer implements ConsumerInterface, LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    private $orderRepository;

    public function __construct(DeclineOrderUseCase $useCase, OrderRepositoryInterface $orderRepository)
    {
        $this->useCase = $useCase;
        $this->orderRepository = $orderRepository;
    }

    abstract protected function getTargetedStates(): array;

    public function execute(AMQPMessage $msg)
    {
        $data = json_decode($msg->getBody(), true);
        $orderUuid = $data['order_id'];

        try {
            $order = $this->orderRepository->getOneByUuid($orderUuid);
            if (!$order || !in_array($order->getState(), $this->getTargetedStates(), true)) {
                // Order does not exist or it is not in that state anymore
                return;
            }
            $request = new DeclineOrderRequest($orderUuid);
            $this->useCase->execute($request);
        } catch (WorkflowException $exception) {
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
