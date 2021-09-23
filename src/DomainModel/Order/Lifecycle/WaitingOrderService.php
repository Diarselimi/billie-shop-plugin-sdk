<?php

namespace App\DomainModel\Order\Lifecycle;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderRiskCheck\CheckResult;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Symfony\Component\Workflow\Registry;

class WaitingOrderService implements SlackClientAwareInterface
{
    use SlackClientAwareTrait;

    private Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function wait(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();

        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_WAITING);

        return;

        $failedRiskCheckNames = array_map(
            function (CheckResult $result) {
                return $result->getName();
            },
            $orderContainer->getRiskCheckResultCollection()->getAllDeclined()
        );

        $message = $this->getSlackMessageFactory()->createSimple(
            'Order was created in waiting state',
            "Order *{$order->getUuid()}* was created in waiting state because of failed risk checks",
            null,
            new SlackMessageAttachmentField('Merchant ID', $order->getMerchantId()),
            new SlackMessageAttachmentField('Order UUID', $order->getUuid()),
            new SlackMessageAttachmentField('Failed Risk Checks', implode(', ', $failedRiskCheckNames)),
            new SlackMessageAttachmentField('Environment', str_replace('_', '', getenv('INSTANCE_SUFFIX')))
        );

        $this->getSlackClient()->sendMessage($message);
    }
}
