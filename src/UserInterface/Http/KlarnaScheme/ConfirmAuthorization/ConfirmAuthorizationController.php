<?php

namespace App\UserInterface\Http\KlarnaScheme\ConfirmAuthorization;

use App\Application\CommandBus;
use App\Application\UseCase\ConfirmOrder\ConfirmOrder;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;

class ConfirmAuthorizationController implements LoggingInterface
{
    use LoggingTrait;

    private CommandBus $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function execute(Request $request): KlarnaResponse
    {
        $command = new ConfirmOrder($request->get('orderId'));

        try {
            $this->bus->process($command);
        } catch (OrderContainerFactoryException $ex) {
            return KlarnaResponse::withErrorMessage('Authorization not found');
        } catch (NotEnabledTransitionException $ex) {
            $this->logSuppressedException($ex, 'Klarna confirm call failed');
        }

        return KlarnaResponse::empty();
    }
}
