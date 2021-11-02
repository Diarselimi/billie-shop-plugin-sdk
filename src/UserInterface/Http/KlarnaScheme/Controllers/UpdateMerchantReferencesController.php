<?php

declare(strict_types=1);

namespace App\UserInterface\Http\KlarnaScheme\Controllers;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ModifyPartnerExternalData\ModifyPartnerExternalDataCommand;
use App\Infrastructure\CommandBus\SynchronousCommandBus\Decorators\DbTransactionCommandBusDecorator;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UpdateMerchantReferencesController
{
    private DbTransactionCommandBusDecorator $commandBus;

    public function __construct(DbTransactionCommandBusDecorator $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(Request $request, string $orderId): JsonResponse
    {
        try {
            $command = new ModifyPartnerExternalDataCommand(
                $orderId,
                $request->get('merchant_reference1'),
                $request->get('merchant_reference2')
            );
        } catch (\TypeError $e) {
            return KlarnaResponse::withErrorMessage('merchant_reference1 is required');
        }

        try {
            $this->commandBus->process($command);
        } catch (OrderNotFoundException $e) {
            return KlarnaResponse::withErrorMessage($e->getMessage());
        }

        return KlarnaResponse::empty();
    }
}
