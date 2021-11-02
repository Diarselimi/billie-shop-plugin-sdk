<?php

declare(strict_types=1);

namespace App\UserInterface\Http\KlarnaScheme\DeleteAuthorization;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DeleteAuthorizationController implements LoggingInterface
{
    use LoggingTrait;

    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(Request $request, string $orderId): JsonResponse
    {
        $command = new DeclineOrderRequest($orderId);

        try {
            $this->commandBus->process($command);
        } catch (OrderNotFoundException $orderNotFoundException) {
            return KlarnaResponse::withErrorMessage('Authorization not found');
        } catch (WorkflowException $ex) {
            $this->logSuppressedException($ex, 'Klarna tried to decline a confirmed authorization.');

            return KlarnaResponse::withErrorMessage('Delete Authorization is not possible.');
        }

        return KlarnaResponse::empty();
    }
}
