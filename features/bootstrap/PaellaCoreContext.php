<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\Customer\CustomerRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behatch\Context\RestContext;

class PaellaCoreContext extends RestContext
{
    use KernelDictionary;

    private $customer;
    private $order;

    /**
     * @Given I have a customer :username with roles :roles and api key :apiKey
     */
    public function iHaveACustomer($username, $roles, $apiKey)
    {
        $customer = (new CustomerEntity())
            ->setName($username)
            ->setApiKey($apiKey)
            ->setAvailableFinancingLimit(1000)
            ->setIsActive(true)
            ->setRoles($roles)
        ;

        $this->getCustomerRepository()->insert($customer);
        $this->customer = $customer;

        $this->request->setHttpHeader('X-Api-User', $customer->getId());
    }

    /**
     * @Given I have an order :externalCode with amounts (:gross, :net, :tax), duration :duration and comment :comment
     */
    public function iHaveAnOrder($externalCode, $gross, $net, $tax, $duration, $comment)
    {
        $person = (new PersonEntity())
            ->setFirstName('test')
            ->setLastName('test')
            ->setEmail('test')
            ->setPhoneNumber('test')
            ->setGender('t')
        ;
        $this->getPersonRepository()->insert($person);

        $deliveryAddress = (new AddressEntity())
            ->setAddition('test')
            ->setHouseNumber('test')
            ->setStreet('test')
            ->setPostalCode('test')
            ->setCity('test')
            ->setCountry('TE')
        ;
        $this->getAddressRepository()->insert($deliveryAddress);

        $debtorAddress = (new AddressEntity())
            ->setAddition('test')
            ->setHouseNumber('test')
            ->setStreet('test')
            ->setPostalCode('test')
            ->setCity('test')
            ->setCountry('TE')
        ;
        $this->getAddressRepository()->insert($debtorAddress);

        $debtor = (new DebtorExternalDataEntity())
            ->setName('test')
            ->setLegalForm('test')
            ->setIndustrySector('test')
            ->setSubindustrySector('test')
            ->setEstablishedCustomer(true)
            ->setAddressId($debtorAddress->getId())
        ;
        $this->getDebtorExternalDataRepository()->insert($debtor);

        $order = (new OrderEntity())
            ->setExternalCode($externalCode)
            ->setAmountNet($net)
            ->setAmountGross($gross)
            ->setAmountTax($tax)
            ->setDuration($duration)
            ->setDebtorPersonId($person->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtor->getId())
            ->setDebtorExternalDataAddressId($debtorAddress->getId())
            ->setState(OrderStateManager::STATE_NEW)
            ->setCustomerId($this->customer->getId())
            ->setExternalComment($comment)
        ;

        $this->getOrderRepository()->insert($order);
        $this->order = $order;
    }

    /**
     * @Then I created the order :externalCode
     */
    public function iCreatedTheOrder($externalCode)
    {
        $this->order = $this->getOrderRepository()->getOneByExternalCode($externalCode, $this->customer->getId());
        $debtor = $this->getDebtorExternalDataRepository()->getOneByIdRaw($this->order->getDebtorExternalDataId());
        $this->order->setDebtorExternalDataAddressId($debtor['address_id']);
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario(AfterScenarioScope $afterScenarioScope)
    {
        if ($this->order) {
            $this->getOrderRepository()->delete($this->order);
        }

        if ($this->customer) {
            $this->getCustomerRepository()->delete($this->customer);
        }
    }

    private function getDebtorExternalDataRepository(): DebtorExternalDataRepositoryInterface
    {
        return $this->get(DebtorExternalDataRepositoryInterface::class);
    }
    
    private function getAddressRepository(): AddressRepositoryInterface
    {
        return $this->get(AddressRepositoryInterface::class);
    }
    
    private function getPersonRepository(): PersonRepositoryInterface
    {
        return $this->get(PersonRepositoryInterface::class);
    }
    
    private function getOrderRepository(): OrderRepositoryInterface
    {
        return $this->get(OrderRepositoryInterface::class);
    }

    private function getCustomerRepository(): CustomerRepositoryInterface
    {
        return $this->get(CustomerRepositoryInterface::class);
    }

    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
