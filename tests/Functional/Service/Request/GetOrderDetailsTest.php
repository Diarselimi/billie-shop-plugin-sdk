<?php

namespace Billie\Sdk\Tests\Functional\Service\Request;

use Billie\Sdk\Exception\OrderNotFoundException;
use Billie\Sdk\Model\Order;
use Billie\Sdk\Model\Request\OrderRequestModel;
use Billie\Sdk\Service\Request\CreateOrderRequest;
use Billie\Sdk\Service\Request\GetOrderDetailsRequest;
use Billie\Sdk\Tests\AbstractTestCase;
use Billie\Sdk\Tests\Helper\BillieClientHelper;
use Billie\Sdk\Tests\Helper\OrderHelper;

class GetOrderDetailsTest extends AbstractTestCase
{
    /**
     * @var Order
     */
    private $createdOrderModel;

    protected function setUp(): void
    {
        $this->createdOrderModel = (new CreateOrderRequest(BillieClientHelper::getClient()))
            ->execute(OrderHelper::createValidOrderModel());
    }

    public function testGetOrderDetails()
    {
        $requestService = new GetOrderDetailsRequest(BillieClientHelper::getClient());
        $order = $requestService->execute(new OrderRequestModel($this->createdOrderModel->getUuid()));
        $this->compareArrays($this->createdOrderModel->toArray(), $order->toArray());
    }

    public function testNotFound()
    {
        $requestService = new GetOrderDetailsRequest(BillieClientHelper::getClient());
        $this->expectException(OrderNotFoundException::class);
        $requestService->execute(new OrderRequestModel(uniqid('invalid-order-id-', false)));
    }
}
