<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\Tests\Helpers\RandomDataTrait;
use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\Assert\Assert;

class RepositoryIntegrationContext implements Context
{
    use KernelDictionary;
    use RandomDataTrait;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^I have an active merchant$/
     */
    public function iHaveAnActiveMerchant()
    {
        $this->createMerchant($this->getRandomMerchantCreationDTO($this->getRandomDebtorCompany()));
    }

    /**
     * @Given I have an order with state :state
     * @Given I have an order with state :state and debtor external ID :ext_id
     * @Given I have an order with state :state and debtor external ID :ext_id, created at :date
     */
    public function iHaveAnOrderWithState($state, $externalId = 'ABC-123', $createdAt = 'now')
    {
        $personId = $this->createPerson($this->getRandomPerson())->getId();
        $addressId = $this->createAddress($this->getRandomAddress())->getId();
        $externalData = $this->getRandomDebtorExternalData($externalId, $addressId, $addressId);
        $externalData->setCreatedAt(new \DateTime($createdAt));
        $externalDataId = $this->createDebtorExternalData($externalData)->getId();

        $order = $this->getRandomOrder(
            $state,
            $this->getLastMerchantCreationDTO()->getMerchant()->getId(),
            $personId,
            $addressId,
            $externalDataId
        );

        $this->createOrder($order);
    }

    /**
     * @Then finding existing debtor external data should give :results results when max minutes is set to :minutes
     */
    public function findingExistingDebtorExternalDataHashShouldGiveAResult($results, $maxMinutes)
    {
        $externalData = $this->getLastDebtorExternalData();
        $service = $this->getContainer()->get(DebtorExternalDataRepositoryInterface::class);
        $newExternalDataId = 0;
        $notNull = boolval($results);

        $result = $service->getOneByHashAndStateNotOlderThanMaxMinutes(
            $externalData->getDataHash(),
            $externalData->getMerchantExternalId(),
            $this->getLastMerchantCreationDTO()->getMerchant()->getId(),
            $newExternalDataId,
            OrderEntity::STATE_DECLINED,
            (int) $maxMinutes
        );

        if ($notNull) {
            Assert::isInstanceOf($result, DebtorExternalDataEntity::class);
        } else {
            Assert::null($result);
        }
    }
}
