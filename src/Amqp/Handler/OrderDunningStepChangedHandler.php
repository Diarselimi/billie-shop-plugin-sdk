<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use Ozean12\Transfer\Message\Order\OrderDunningStepChanged;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderDunningStepChangedHandler implements MessageHandlerInterface
{
    private $useCase;

    public function __construct(UpdateMerchantWithOrderDunningStepUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(OrderDunningStepChanged $message)
    {
        $request = new UpdateMerchantWithOrderDunningStepRequest(
            $message->getUuid(),
            $message->getInvoiceUuid(),
            $message->getDunningStep()
        );

        $this->useCase->execute($request);
    }
}
