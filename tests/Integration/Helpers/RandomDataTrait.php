<?php

declare(strict_types=1);

namespace App\Tests\Integration\Helpers;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

trait RandomDataTrait
{
    private $data = [
        'last_merchant_creation_dto' => null,
        'last_order' => null,
        'last_person' => null,
        'last_address' => null,
        'last_debtor_external_data' => null,
    ];

    private function getLastMerchantCreationDTO(): MerchantCreationDTO
    {
        Assert::isInstanceOf($this->data['last_merchant_creation_dto'], MerchantCreationDTO::class);

        return $this->data['last_merchant_creation_dto'];
    }

    private function getLastDebtorExternalData(): DebtorExternalDataEntity
    {
        Assert::isInstanceOf($this->data['last_debtor_external_data'], DebtorExternalDataEntity::class);

        return $this->data['last_debtor_external_data'];
    }

    private function getRandomPerson(): PersonEntity
    {
        return (new PersonEntity())
            ->setFirstName('John')
            ->setLastName('Smith')
            ->setEmail('smith@test.test')
            ->setPhoneNumber('+49' . mt_rand(17000000000, 18999999999));
    }

    private function getRandomOrder(
        string $state,
        int $merchantId,
        int $personId,
        int $deliveryAddressId,
        int $debtorExternalDataId
    ): OrderEntity {
        return (new OrderEntity())
            ->setExternalCode('EXT-' . Uuid::uuid4()->toString())
            ->setState($state)
            ->setAmountForgiven(0)
            ->setDebtorPersonId($personId)
            ->setDeliveryAddressId($deliveryAddressId)
            ->setDebtorExternalDataId($debtorExternalDataId)
            ->setMerchantDebtorId(null)
            ->setMerchantId($merchantId)
            ->setPaymentId(Uuid::uuid4()->toString())
            ->setUuid(Uuid::uuid4()->toString())
            ->setCreationSource(OrderEntity::CREATION_SOURCE_API);
    }

    private function getRandomAddress(): AddressEntity
    {
        return (new AddressEntity())
            ->setStreet('Fake Street')
            ->setHouseNumber(mt_rand(1, 200) . '')
            ->setPostalCode(mt_rand(10000, 19999) . '')
            ->setCity('Berlin')
            ->setCountry('DE');
    }

    private function getRandomDebtorExternalData(
        string $externalId,
        int $addressId,
        int $billingAddressId
    ): DebtorExternalDataEntity {
        return (new DebtorExternalDataEntity())
            ->setName("Fake Debtor GmbH")
            ->setTaxId('TAXID-' . mt_rand(100, 10000))
            ->setTaxNumber('TAXNUM-' . mt_rand(100, 10000))
            ->setRegistrationNumber('REGNUM-' . mt_rand(100, 10000))
            ->setRegistrationCourt('REGCOURT-' . mt_rand(100, 10000))
            ->setLegalForm((string) $this->getRandomLegalForm()['code'])
            ->setAddressId($addressId)
            ->setBillingAddressId($billingAddressId)
            ->setMerchantExternalId($externalId)
            ->setDataHash('HASH-' . $externalId);
    }

    private function getRandomLegalForm(): array
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../src/Resources/legal_forms.json'), true);

        return $json['items'][array_rand($json['items'])];
    }

    private function getRandomDebtorCompany(): DebtorCompany
    {
        $address = new AddressEntity();
        $address->setStreet('Fake street')
            ->setHouseNumber(mt_rand(1, 200) . '')
            ->setPostalCode(mt_rand(10000, 19999) . '')
            ->setCity('Berlin')
            ->setCountry('DE');

        $company = (new DebtorCompany())
            ->setId(mt_rand(100, 10000))
            ->setUuid(Uuid::uuid4()->toString())
            ->setName("Fake Merchant GmbH")
            ->setAddress($address);

        return $company;
    }

    private function getRandomMerchantCreationDTO(DebtorCompany $company): MerchantCreationDTO
    {
        $merchantLimit = mt_rand(10000, 20000);
        $debtorLimit = mt_rand(1000, 2000);

        $merchant = new MerchantCreationDTO(
            $company,
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            $merchantLimit,
            $debtorLimit
        );

        $merchant->setIsOnboardingComplete(true);

        return $merchant;
    }

    private function getRandomOrderEntity(): OrderEntity
    {
        $order = new OrderEntity();
        $this->fillObject($order);
        $merchantDebtor = new MerchantDebtorEntity();
        $this->fillObject($merchantDebtor);

        $merchantId = $this->getMerchantFromSeed()->getId();
        $merchantDebtor->setMerchantId((string) $merchantId);

        $address = new AddressEntity();
        $this->fillObject($address);
        $this->createAddress($address);

        $person = new PersonEntity();
        $this->fillObject($person);
        $this->createPerson($person);

        $externalData = new DebtorExternalDataEntity();
        $this->fillObject($externalData);
        $externalData
            ->setAddressId($address->getId())
            ->setBillingAddressId($address->getId());
        $this->createDebtorExternalData($externalData);

        $this->createMerchantDebtor($merchantDebtor);
        $order->setMerchantDebtorId($merchantDebtor->getId());
        $order->setMerchantId($merchantId);
        $order->setDebtorExternalDataId($externalData->getId());
        $order->setDeliveryAddressId($address->getId());
        $order->setDebtorPersonId($person->getId());

        $this->createOrder($order);

        return $order;
    }

    /**
     * @param  int                    $orderId
     * @return OrderRiskCheckEntity[]
     */
    private function generateSomeFailingOrderRiskChecks(int $orderId): array
    {
        $riskChecks = $this->getRiskChecksDefinitionsFromSeed();
        $uniqueFailedRiskChecks = [];

        foreach ($riskChecks as $check) {
            $orderRiskCheck = new OrderRiskCheckEntity();
            $this->fillObject($orderRiskCheck);
            $orderRiskCheck->setOrderId($orderId);
            $orderRiskCheck->setRiskCheckDefinition($check);
            $this->createOrderRiskCheck($orderRiskCheck);

            if (!$orderRiskCheck->isPassed()) {
                $uniqueFailedRiskChecks[$orderRiskCheck->getRiskCheckDefinition()->getName()] = $orderRiskCheck;
            }
        }

        return $uniqueFailedRiskChecks;
    }

    private function createDebtorExternalData(DebtorExternalDataEntity $entity): DebtorExternalDataEntity
    {
        $this->getContainer()->get(DebtorExternalDataRepositoryInterface::class)->insert($entity);
        $this->data['last_debtor_external_data'] = $entity;

        return $entity;
    }

    private function createOrder(OrderEntity $entity): OrderEntity
    {
        $this->getContainer()->get(OrderRepositoryInterface::class)->insert($entity);
        $this->data['last_order'] = $entity;

        return $entity;
    }

    private function createAddress(AddressEntity $entity): AddressEntity
    {
        $this->getContainer()->get(AddressRepositoryInterface::class)->insert($entity);
        $this->data['last_address'] = $entity;

        return $entity;
    }

    private function createPerson(PersonEntity $entity): PersonEntity
    {
        $this->getContainer()->get(PersonRepositoryInterface::class)->insert($entity);
        $this->data['last_person'] = $entity;

        return $entity;
    }

    private function createMerchant(MerchantCreationDTO $merchantCreationDTO): MerchantCreationDTO
    {
        $this->getContainer()->get(MerchantCreationService::class)->create($merchantCreationDTO);
        $this->data['last_merchant_creation_dto'] = $merchantCreationDTO;

        return $merchantCreationDTO;
    }

    private function createMerchantDebtor(MerchantDebtorEntity $entity): MerchantDebtorEntity
    {
        $this->getContainer()->get(MerchantDebtorRepositoryInterface::class)->insert($entity);

        return $entity;
    }

    private function createOrderRiskCheck(OrderRiskCheckEntity $entity): OrderRiskCheckEntity
    {
        $this->getContainer()->get(OrderRiskCheckRepositoryInterface::class)->insert($entity);

        return $entity;
    }
}
