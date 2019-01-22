<?php

namespace spec\App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;
use PhpSpec\ObjectBehavior;

class UpdateMerchantWithOrderDunningStepUseCaseSpec extends ObjectBehavior
{
    const ORDER_UUID = 'dwokwdowdo22ok2ok2o2k';

    const ORDER_EXTENRAL_ID = 'test';

    const MERCHANT_ID = 1;

    const INVALID_STEP = 'invalid dunning step';

    public function let(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        NotificationSender $notificationSender
    ) {
        $this->beConstructedWith($orderRepository, $merchantRepository, $notificationSender);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateMerchantWithOrderDunningStepUseCase::class);
    }

    public function it_throws_exception_if_order_was_not_found(OrderRepositoryInterface $orderRepository)
    {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, 's');

        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_sends_notification_to_merchant_webhook_with_dunning_step(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        NotificationSender $notificationSender,
        OrderEntity $orderEntity,
        MerchantEntity $merchantEntity
    ) {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, 'Dunning');

        $orderEntity->getExternalCode()->willReturn(self::ORDER_EXTENRAL_ID);
        $orderEntity->getMerchantId()->willReturn(self::MERCHANT_ID);
        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn($orderEntity);

        $merchantEntity->getId()->willReturn(self::MERCHANT_ID);
        $merchantRepository->getOneById(self::MERCHANT_ID)->shouldBeCalled()->willReturn($merchantEntity);

        $notification = (new NotificationDTO())->setEventName('Dunning')->setOrderId(self::ORDER_EXTENRAL_ID);
        $notificationSender
            ->send($merchantEntity, $notification)
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }
}
