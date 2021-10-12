<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\Sandbox\SandboxMerchantCreationService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CustomerCreatedHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private $merchantRepository;

    private $creationService;

    private $paellaSandboxUrl;

    public function __construct(
        MerchantRepository $merchantRepopository,
        SandboxMerchantCreationService $creationService,
        string $paellaSandboxUrl
    ) {
        $this->merchantRepository = $merchantRepopository;
        $this->creationService = $creationService;
        $this->paellaSandboxUrl = $paellaSandboxUrl;
    }

    public function __invoke(CustomerCreated $message)
    {
        if (empty($this->paellaSandboxUrl)) {
            return;
        }

        $merchant = $this->merchantRepository->getOneByCompanyUuid($message->getCompanyUuid());

        if (!$merchant) {
            $this->logWarning(sprintf('Merchant does not exist, company uuid %s', $message->getCompanyUuid()));

            return;
        }

        if (!$merchant->getSandboxPaymentUuid()) {
            try {
                $this->creationService->create($merchant);
                $this->logInfo(sprintf('Sandbox merchant successfully created, merchant id %s', $merchant->getId()));
            } catch (\Exception $exception) {
                $this->logSuppressedException($exception, $exception->getMessage());
            }
        } else {
            $this->logWarning(sprintf('Sandbox merchant already exists, merchant id %s', $merchant->getId()));
        }
    }
}
