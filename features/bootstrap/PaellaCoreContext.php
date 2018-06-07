<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
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

class PaellaCoreContext extends MinkContext
{
    use KernelDictionary;

    private $alfred;
    private $borscht;
    private $risky;
    private static $countAlfred = 1;
    private static $countBorscht = 1;
    private static $countRisky = 1;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->alfred = new MockWebServer(8024);
        $this->borscht = new MockWebServer(8025);
        $this->risky = new MockWebServer(8026);

        $this->getMerchantRepository()->insert(
            (new MerchantEntity())
                ->setName('Behat User')
                ->setIsActive(true)
                ->setRoles('["ROLE_NOTHING"]')
                ->setAvailableFinancingLimit(10000)
                ->setApiKey('test')
                ->setCompanyId('1')
        );
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario(AfterScenarioScope $afterScenarioScope)
    {
        $this->alfred->stop();
        $this->borscht->stop();
        $this->risky->stop();
        $this->getConnection()->exec('
            DELETE FROM order_transitions;
            DELETE FROM risk_checks;
            DELETE FROM orders;
            DELETE FROM persons;
            DELETE FROM debtor_external_data;
            DELETE FROM addresses;
            DELETE FROM merchants_debtors;
            DELETE FROM merchants;
            ALTER TABLE merchants AUTO_INCREMENT = 1;
        ');
    }

    /**
     * @Given I start alfred
     */
    public function iStartAlfred()
    {
        $this->alfred->start();
    }

    /**
     * @Given I start borscht
     */
    public function iStartBorscht()
    {
        $this->borscht->start();
    }

    /**
     * @Given I start risky
     */
    public function iStartRisky()
    {
        $this->risky->start();
    }

    /**
     * @Given I get from alfred :url endpoint response with status :status and body
     */
    public function iGetFromAlfredEndpointResponse(string $url, int $status, PyStringNode $body)
    {
        $this->alfred->start();
        $this->alfred->setResponseOfPath($url, new Response($body, ['X-Count' => self::$countAlfred++], $status));
    }

    /**
     * @Given I get from borscht :url endpoint response with status :status and body
     */
    public function iGetFromBorschtEndpointResponse(string $url, int $status, PyStringNode $body)
    {
        $this->borscht->start();
        $this->borscht->setResponseOfPath($url, new Response($body, ['X-Count' => self::$countBorscht++], $status));
    }

    /**
     * @Given I get from risky :url endpoint response with status :status and body
     */
    public function iGetFromRiskyEndpointResponse(string $url, int $status, PyStringNode $body)
    {
        $this->risky->start();
        $this->risky->setResponseOfPath($url, new Response($body, ['X-Count' => self::$countRisky++], $status));
    }

    /**
     * @Given I have a(n) :state order :externalCode with amounts :gross/:net/:tax, duration :duration and comment :comment
     */
    public function iHaveAnOrder($state, $externalCode, $gross, $net, $tax, $duration, $comment)
    {
        $person = (new PersonEntity())
            ->setFirstName('test')
            ->setLastName('test')
            ->setEmail('test')
            ->setPhoneNumber('test')
            ->setGender('t');
        $this->getPersonRepository()->insert($person);

        $deliveryAddress = (new AddressEntity())
            ->setAddition('test')
            ->setHouseNumber('test')
            ->setStreet('test')
            ->setPostalCode('test')
            ->setCity('test')
            ->setCountry('TE');
        $this->getAddressRepository()->insert($deliveryAddress);

        $debtorAddress = (new AddressEntity())
            ->setAddition('test')
            ->setHouseNumber('test')
            ->setStreet('test')
            ->setPostalCode('test')
            ->setCity('test')
            ->setCountry('TE');
        $this->getAddressRepository()->insert($debtorAddress);

        $debtor = (new DebtorExternalDataEntity())
            ->setName('test')
            ->setLegalForm('test')
            ->setIndustrySector('test')
            ->setSubindustrySector('test')
            ->setEstablishedCustomer(true)
            ->setAddressId($debtorAddress->getId());
        $this->getDebtorExternalDataRepository()->insert($debtor);

        $order = (new OrderEntity())
            ->setExternalCode($externalCode)
            ->setState($state)
            ->setAmountNet($net)
            ->setAmountGross($gross)
            ->setAmountTax($tax)
            ->setDuration($duration)
            ->setDebtorPersonId($person->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtor->getId())
            ->setExternalComment($comment)
            ->setMerchantDebtorId('1')
            ->setMerchantId('1');

        $this->getOrderRepository()->insert($order);
    }

    /**
     * @Given Order :externalCode was shipped at :date
     */
    public function orderWasShipped($externalCode, $date)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($externalCode, 1);
        $order->setShippedAt(new \DateTime($date));
        $this->getOrderRepository()->update($order);
    }

    /**
     * @Given the order :orderId is :state
     */
    public function orderIsInState($orderId, $state)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }
        if ($order->getState() !== $state) {
            throw new RuntimeException(sprintf(
                'Order is in %s state, but %s was expected.',
                $order->getState(),
                $state
            ));
        }
    }

    /**
     * @Given the order :orderId :key is :value
     */
    public function orderDurationIs($orderId, $key, $value)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }
        $actual = $order->{'get' . ucfirst($key)}();
        if ($actual != $value) {
            throw new RuntimeException(sprintf(
                'Order %s is %s state, but %s was expected.',
                $key,
                $actual,
                $value
            ));
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

    private function getMerchantRepository(): MerchantRepositoryInterface
    {
        return $this->get(MerchantRepositoryInterface::class);
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
