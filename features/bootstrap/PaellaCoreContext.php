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
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class PaellaCoreContext extends MinkContext implements Context
{
    use KernelDictionary;

    private $server;
    private static $count = 1;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->server = new MockWebServer(8024);

        $this->getCustomerRepository()->insert((new CustomerEntity())
            ->setName('Behat User')
            ->setIsActive(true)
            ->setRoles('["ROLE_NOTHING"]')
            ->setAvailableFinancingLimit(10000)
            ->setApiKey('test')
        );
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario(AfterScenarioScope $afterScenarioScope)
    {
        $this->server->stop();
        $this->getConnection()->exec('
            DELETE FROM risk_checks;
            DELETE FROM orders;
            DELETE FROM companies;
            DELETE FROM persons;
            DELETE FROM debtor_external_data;
            DELETE FROM addresses;
            DELETE FROM customers;
            ALTER TABLE customers AUTO_INCREMENT = 1;
        ');
    }

    /**
     * @Given I get from alfred :url endpoint response with status :status and body
     */
    public function iGetFromAlfredEndpointResponse(string $url, int $status, PyStringNode $body)
    {
        $this->server->start();
        $this->server->setResponseOfPath($url, new Response($body, ['X-Count' => self::$count++], $status));
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
            ->setState(OrderStateManager::STATE_NEW)
            ->setCustomerId(1)
            ->setExternalComment($comment)
        ;

        $this->getOrderRepository()->insert($order);
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

    private function getConnection(): \PDO
    {
        return $this->get('paella_core.pdo');
    }

    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
