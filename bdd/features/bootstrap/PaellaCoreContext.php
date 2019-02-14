<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\PDO\PDO;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class PaellaCoreContext extends MinkContext
{
    use KernelDictionary;

    const MERCHANT_ID = 1;

    private $alfred;

    private $borscht;

    private static $countAlfred = 1;

    private static $countBorscht = 1;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->alfred = new MockWebServer(8024);
        $this->borscht = new MockWebServer(8025);

        $this->getMerchantRepository()->insert(
            (new MerchantEntity())
                ->setName('Behat User')
                ->setIsActive(true)
                ->setRoles('["ROLE_NOTHING"]')
                ->setPaymentMerchantId('f2ec4d5e-79f4-40d6-b411-31174b6519ac')
                ->setAvailableFinancingLimit(10000)
                ->setApiKey('test')
                ->setCompanyId('10')
        );

        $scoreThreshold = (new ScoreThresholdsConfigurationEntity())
            ->setCrefoLowScoreThreshold(350)
            ->setCrefoHighScoreThreshold(400)
            ->setSchufaLowScoreThreshold(200)
            ->setSchufaAverageScoreThreshold(220)
            ->setSchufaHighScoreThreshold(260)
            ->setSchufaSoleTraderScoreThreshold(235)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
        $this->getScoreThresholdsConfigurationRepository()->insert($scoreThreshold);

        $this->getMerchantSettingsRepository()->insert(
            (new MerchantSettingsEntity())
                ->setMerchantId(1)
                ->setDebtorFinancingLimit(10000)
                ->setMinOrderAmount(0)
                ->setScoreThresholdsConfigurationId($scoreThreshold->getId())
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime())
        );
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario(AfterScenarioScope $afterScenarioScope)
    {
        $this->alfred->stop();
        $this->borscht->stop();

        $this->getConnection()->exec('
            DELETE FROM order_transitions;
            DELETE FROM order_invoices;
            DELETE FROM order_identifications;
            DELETE FROM risk_checks;
            DELETE FROM orders;
            DELETE FROM persons;
            DELETE FROM debtor_external_data;
            DELETE FROM addresses;
            DELETE FROM merchants_debtors;
            DELETE FROM merchant_settings;
            DELETE FROM merchants;
            DELETE FROM merchants_debtors;
            DELETE FROM score_thresholds_configuration;
            ALTER TABLE merchants AUTO_INCREMENT = 1;
            ALTER TABLE orders AUTO_INCREMENT = 1;
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
            ->setCity('test22')
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

        $merchantDebtor = (new MerchantDebtorEntity())
            ->setMerchantId(self::MERCHANT_ID)
            ->setDebtorId('2')
            ->setPaymentDebtorId(1)
            ->setFinancingLimit(1000)
        ;

        $this->getMerchantDebtorRepository()->insert($merchantDebtor);

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
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setMerchantId('1')
            ->setPaymentId('test')
        ;

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
            if ($state === 'null') {
                return;
            }

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

    /**
     * @Given /^The order "([^"]*)" was already marked as fraud$/
     */
    public function theOrderWasAlreadyMarkedAsFraud($orderExternalCode)
    {
        $order = $order = $this->getOrder($orderExternalCode);

        $order->setMarkedAsFraudAt(new DateTime());

        $this->getOrderRepository()->update($order);
    }

    /**
     * @Given The order :orderExternalCode has UUID :uuid
     */
    public function theOrderHasUUID($orderExternalCode, $uuid)
    {
        $this->getConnection()
             ->prepare("UPDATE orders SET uuid = :uuid WHERE external_code = :orderExternalCode")
             ->execute([':uuid' => $uuid, ':orderExternalCode' => $orderExternalCode]);
    }

    /**
     * @Given /^The order "([^"]*)" is marked as fraud$/
     */
    public function theOrderIsMarkedAsFraud($orderExternalCode)
    {
        $order = $this->getOrder($orderExternalCode);

        Assert::notNull($order->getMarkedAsFraudAt());
    }

    /**
     * @Given I start :consumerName consumer to consume :messagesCount message
     */
    public function iStartOrder_debtor_identification_vconsumerToConsumerMessage($consumerName, $messagesCount)
    {
        $command = __DIR__ . "/../../../bin/console --env=test rabbitmq:consumer -m {$messagesCount} {$consumerName}";

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @When I push message to :queueName queue and routing key :routingKey with the following content:
     */
    public function iPushMessageToOrder_debtor_identification_v2_paellaQueueWithTheFollowingContent(
        $queueName,
        $routingKey,
        PyStringNode $string
    ) {
        /** @var ProducerInterface $producerService */
        $producerService = $this->get(sprintf('old_sound_rabbit_mq.%s_producer', $queueName));

        $producerService->publish($string, $routingKey);
    }

    /**
     * @Given order_identifications table should have a new record with:
     */
    public function order_identificationsTablShouldHaveANewRecordWith(\Behat\Gherkin\Node\TableNode $table)
    {
        $repo = $this->getOrderIdentificationRepository();

        foreach ($table as $row) {
            $record = $repo->findOneByOrderAndCompanyIds(
                (int) $row['order_id'],
                !empty($row['v1_company_id']) ? (int) $row['v1_company_id'] : null,
                !empty($row['v2_company_id']) ? (int) $row['v2_company_id'] : null
            );

            Assert::notNull($record);
        }
    }

    private function getOrder($orderExternalCode, $customerId = self::MERCHANT_ID): OrderEntity
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderExternalCode, $customerId);

        if ($order === null) {
            throw new RuntimeException('Order not found');
        }

        return $order;
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

    private function getMerchantSettingsRepository(): MerchantSettingsRepositoryInterface
    {
        return $this->get(MerchantSettingsRepositoryInterface::class);
    }

    private function getMerchantDebtorRepository(): MerchantDebtorRepositoryInterface
    {
        return $this->get(MerchantDebtorRepositoryInterface::class);
    }

    private function getScoreThresholdsConfigurationRepository(): ScoreThresholdsConfigurationRepositoryInterface
    {
        return $this->get(ScoreThresholdsConfigurationRepositoryInterface::class);
    }

    private function getOrderIdentificationRepository(): OrderIdentificationRepositoryInterface
    {
        return $this->get(OrderIdentificationRepositoryInterface::class);
    }

    private function getConnection(): \PDO
    {
        return $this->get(PDO::class);
    }

    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
