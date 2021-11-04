<?php

namespace App\UserInterface\Http\KlarnaScheme\CancelAuthorization;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CancelOrder\CancelOrderException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Request;

class CancelAuthorizationController implements LoggingInterface
{
    use LoggingTrait;

    private CommandBus $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function execute(Request $request): KlarnaResponse
    {
        $command = new CancelOrderRequest($request->get('orderId'));

        try {
            $this->bus->process($command);
        } catch (OrderNotFoundException $ex) {
            return KlarnaResponse::withErrorMessage('Authorization not found');
        } catch (WorkflowException | CancelOrderException $ex) {
            $this->logSuppressedException($ex, 'Klarna cancel authorization call failed');
        }

        return KlarnaResponse::empty();
    }
}
