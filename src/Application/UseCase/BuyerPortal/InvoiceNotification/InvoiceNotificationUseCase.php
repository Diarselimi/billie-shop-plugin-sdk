<?php

declare(strict_types=1);

namespace App\Application\UseCase\BuyerPortal\InvoiceNotification;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Smaug\Client\Dto\ResourceToken;
use Ozean12\Smaug\Client\Exception\AccessDeniedHttpException;
use Ozean12\Smaug\Client\Exception\SmaugClientException;
use Ozean12\Smaug\Client\SmaugClientInterface;
use Ozean12\Transfer\Message\BuyerPortal\BuyerPortalInvoiceNotificationRequested;
use Ozean12\Transfer\Shared\UnregisteredUser;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceNotificationUseCase implements LoggingInterface
{
    use LoggingTrait;

    private SmaugClientInterface $resourceTokenService;

    private MessageBusInterface $messageBus;

    public function __construct(SmaugClientInterface $resourceTokenService, MessageBusInterface $messageBus)
    {
        $this->resourceTokenService = $resourceTokenService;
        $this->messageBus = $messageBus;
    }

    public function execute(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $debtorPerson = $orderContainer->getDebtorPerson();
        $email = $debtorPerson->getEmail();

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errorMessage = 'Buyer Portal: invalid email found for debtor person ID ' .
                $debtorPerson->getId() . '. BP notification will not be sent for order ID '
                . $orderContainer->getOrder()->getId();

            $this->logSuppressedException(new \RuntimeException($errorMessage), $errorMessage);

            return;
        }

        try {
            $token = $this->resourceTokenService->createResourceTokenIfNotFound(
                Uuid::fromString($orderContainer->getDebtorCompany()->getUuid()),
                $email,
                ResourceToken::RESOURCE_TYPE_BUYER_PORTAL_AP
            );
        } catch (AccessDeniedHttpException $exception) {
            $this->logger->info('Buyer Portal invoice notification not sent. Token revoked for: ' . $email);

            return;
        } catch (SmaugClientException $exception) {
            $this->logSuppressedException(
                $exception,
                'Buyer Portal token retrieval failed: ' . $exception->getMessage()
            );

            return;
        }

        $message = (new BuyerPortalInvoiceNotificationRequested())
            ->setUser(
                (new UnregisteredUser())
                    ->setEmail($email)
                    ->setFirstName($debtorPerson->getFirstName())
                    ->setLastName($debtorPerson->getLastName())
                    ->setGender($debtorPerson->getGender())
            )
            ->setDebtorName($orderContainer->getDebtorCompany()->getName())
            ->setCreditorName($orderContainer->getMerchant()->getName())
            ->setToken($token->getToken())
            ->setInvoiceUuid($invoice->getUuid())
            ->setInvoiceAmount((int) ($invoice->getOutstandingAmount()->getMoneyValue() * 100));

        $this->messageBus->dispatch($message);

        $this->logger->info('Buyer Portal invoice notification sent for: ' . $email);
    }
}
