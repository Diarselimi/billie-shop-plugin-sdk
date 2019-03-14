<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsEntity;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\PDO\PDO;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class PaellaCoreContext extends MinkContext
{
    use KernelDictionary, MockServerTrait;

    const MERCHANT_ID = 1;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->setServer($kernel);

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
                ->setDebtorIdentificationAlgorithm(CompaniesServiceInterface::DEBTOR_IDENTIFICATION_ALGORITHM_V1)
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime())
        );
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario(AfterScenarioScope $afterScenarioScope)
    {
        $this->stopServer();
        $this->getConnection()->exec('
            SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE order_transitions;
            TRUNCATE order_invoices;
            TRUNCATE order_identifications;
            TRUNCATE order_risk_checks;
            TRUNCATE orders;
            TRUNCATE persons;
            TRUNCATE debtor_external_data;
            TRUNCATE addresses;
            TRUNCATE merchants_debtors;
            TRUNCATE merchant_settings;
            TRUNCATE merchant_risk_check_settings;
            TRUNCATE merchants;
            TRUNCATE merchants_debtors;
            TRUNCATE score_thresholds_configuration;
            TRUNCATE risk_check_definitions;
            ALTER TABLE merchants AUTO_INCREMENT = 1;
            ALTER TABLE orders AUTO_INCREMENT = 1;
            SET FOREIGN_KEY_CHECKS = 1;
        ');
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
            ->setMerchantExternalId('ext_id')
            ->setAddressId($debtorAddress->getId());
        $this->getDebtorExternalDataRepository()->insert($debtor);

        $merchantDebtor = (new MerchantDebtorEntity())
            ->setMerchantId(self::MERCHANT_ID)
            ->setDebtorId('1')
            ->setPaymentDebtorId('test')
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
     * @Given the order :orderId is in state :state
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
     * @Given consumer :consumerName is empty
     */
    public function consumerOrder_debtor_identification_visEmpty(string $consumerName)
    {
        $command = __DIR__ . "/../../../bin/console rabbitmq:purge --no-confirmation {$consumerName}";

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @When I push message to :queueName queue and routing key :routingKey with the following content:
     */
    public function iPushMessageMessageToQueueAndRoutingKeyWithTheFollowingContent(
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
    public function orderIdentificationsTableShouldHaveANewRecordWith(\Behat\Gherkin\Node\TableNode $table)
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

    /**
     * @Given /^The following risk check definitions exist:$/
     */
    public function theFollowingRiskCheckDefinitionsExist1(TableNode $table)
    {
        foreach ($table as $row) {
            $riskCheckDefinition = (new RiskCheckDefinitionEntity())->setName($row['name']);

            $this->getRiskCheckDefinitionRepository()->insert($riskCheckDefinition);
        }
    }

    /**
     * @Given The following merchant risk check settings exist for merchant :merchantId:
     */
    public function theFollowingRiskCheckDefinitionsExist(TableNode $table, $merchantId)
    {
        foreach ($table as $row) {
            $riskCheckDefinition = $this->getRiskCheckDefinitionRepository()->getByName($row['risk_check_name']);

            $this->getMerchantRiskCheckSettingsRepository()->insert(
                (new MerchantRiskCheckSettingsEntity())
                    ->setMerchantId($merchantId)
                    ->setRiskCheckDefinition($riskCheckDefinition)
                    ->setEnabled($row['enabled'] === '1' ? true : false)
                    ->setDeclineOnFailure($row['decline_on_failure'] === '1' ? true : false)
            );
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

    private function getRiskCheckDefinitionRepository(): RiskCheckDefinitionRepositoryInterface
    {
        return $this->get(RiskCheckDefinitionRepositoryInterface::class);
    }

    private function getMerchantRiskCheckSettingsRepository(): MerchantRiskCheckSettingsRepositoryInterface
    {
        return $this->get(MerchantRiskCheckSettingsRepositoryInterface::class);
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
