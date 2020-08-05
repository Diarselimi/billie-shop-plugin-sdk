<?php

declare(strict_types=1);

namespace spec\App\Amqp\Handler;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Sandbox\SandboxMerchantCreationService;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class CustomerCreatedHandlerSpec extends ObjectBehavior
{
    public function let(
        MerchantRepositoryInterface $merchantRepo,
        SandboxMerchantCreationService $creationService,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($merchantRepo, $creationService, 'dymmy-sandbox-url');
        $this->setLogger($logger);
    }

    public function it_should_make_creation_call(
        MerchantRepositoryInterface $merchantRepo,
        SandboxMerchantCreationService $creationService
    ) {
        $companyUuid = 'dummy-uuid';
        $message = new CustomerCreated();
        $message->setCompanyUuid($companyUuid);

        $merchant = new MerchantEntity();

        $merchantRepo
            ->getOneByCompanyUuid($companyUuid)
            ->willReturn($merchant);

        $creationService
            ->create($merchant)
            ->shouldBeCalledOnce();

        $this->__invoke($message);
    }

    public function it_should_not_make_creation_call(
        MerchantRepositoryInterface $merchantRepo,
        SandboxMerchantCreationService $creationService
    ) {
        $companyUuid = 'dummy-uuid';
        $message = new CustomerCreated();
        $message->setCompanyUuid($companyUuid);

        $merchantRepo
            ->getOneByCompanyUuid($companyUuid)
            ->willReturn(null);

        $creationService
            ->create(null)
            ->shouldNotBeCalled();

        $this->__invoke($message);
    }
}
