<?php

declare(strict_types=1);

namespace App\UserInterface\Http\KlarnaScheme\CaptureAuthorizations;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CreateInvoice\CreateInvoiceCommand;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderAmountExceededException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Helper\Uuid\UuidGenerator;
use App\Http\RequestTransformer\CreateInvoice\ShippingInfoFactory;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CaptureAuthorizationController implements LoggingInterface
{
    use LoggingTrait;

    private CommandBus $bus;

    private UuidGenerator $uuidGenerator;

    private ShippingInfoFactory $shippingInfoFactory;

    public function __construct(CommandBus $bus, UuidGenerator $uuidGenerator, ShippingInfoFactory $shippingInfoFactory)
    {
        $this->bus = $bus;
        $this->uuidGenerator = $uuidGenerator;
        $this->shippingInfoFactory = $shippingInfoFactory;
    }

    public function execute(Request $request, string $orderId): JsonResponse
    {
        try {
            $command = new CreateInvoiceCommand(
                $orderId,
                $request->get('amount'),
                $request->get('capture_id'),
                $request->get('captured_at'),
                $this->uuidGenerator->uuid(),
                $this->shippingInfoFactory->create($request)
            );

            $this->bus->process($command);
        } catch (OrderNotFoundException $ex) {
            return KlarnaResponse::withErrorMessage('Authorization not found');
        } catch (\TypeError $ex) {
            return KlarnaResponse::withErrorMessage('Request data are missing');
        } catch (WorkflowException | ShipOrderAmountExceededException | ShipOrderMerchantFeeNotSetException $ex) {
            $this->logSuppressedException($ex, 'Klarna capture call failed.', [LoggingInterface::KEY_SOBAKA => ['orderId' => $command->getOrderId()]]);

            return KlarnaResponse::withErrorMessage('Capture is not possible');
        }

        return KlarnaResponse::empty();
    }
}
