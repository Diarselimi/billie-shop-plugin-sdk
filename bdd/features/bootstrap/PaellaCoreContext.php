<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsEntity;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class PaellaCoreContext extends MinkContext
{
    use KernelDictionary;

    private $connection;

    /**
     * @var MerchantEntity
     */
    private $merchant;

    private const DEBTOR_UUID = 'ad74bbc4-509e-47d5-9b50-a0320ce3d715';

    public function __construct(KernelInterface $kernel, PdoConnection $connection)
    {
        $this->kernel = $kernel;
        $this->connection = $connection;

        $this->getConnection()->exec('SET SESSION wait_timeout=30; SET SESSION max_connections = 500;');
        $this->cleanUpScenario();

        register_shutdown_function(function () {
            $this->getConnection()->reconnect();
            $this->cleanUpScenario();
        });

        $this->merchant = (new MerchantEntity())
                ->setName('Behat User')
                ->setIsActive(true)
                ->setRoles(['ROLE_NOTHING'])
                ->setPaymentMerchantId('f2ec4d5e-79f4-40d6-b411-31174b6519ac')
                ->setAvailableFinancingLimit(10000)
                ->setApiKey('test')
                ->setCompanyId('10')
                ->setOauthClientId('oauthClientId');

        $this->getMerchantRepository()->insert($this->merchant);

        $scoreThreshold = (new ScoreThresholdsConfigurationEntity())
            ->setCrefoLowScoreThreshold(350)
            ->setCrefoHighScoreThreshold(400)
            ->setSchufaLowScoreThreshold(200)
            ->setSchufaAverageScoreThreshold(220)
            ->setSchufaHighScoreThreshold(260)
            ->setSchufaSoleTraderScoreThreshold(235)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
        $this->getScoreThresholdsConfigurationRepository()->insert($scoreThreshold);

        $this->getMerchantSettingsRepository()->insert(
            (new MerchantSettingsEntity())
                ->setMerchantId($this->merchant->getId())
                ->setInitialDebtorFinancingLimit(10000)
                ->setDebtorFinancingLimit(10000)
                ->setMinOrderAmount(0)
                ->setScoreThresholdsConfigurationId($scoreThreshold->getId())
                ->setUseExperimentalDebtorIdentification(false)
                ->setDebtorForgivenessThreshold(1.0)
                ->setInvoiceHandlingStrategy('http')
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
        );
    }

    /**
     * @AfterScenario
     */
    public function cleanUpScenario()
    {
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
            TRUNCATE merchant_users;
            TRUNCATE merchants;
            TRUNCATE merchant_debtor_financial_details;
            TRUNCATE merchants_debtors;
            TRUNCATE score_thresholds_configuration;
            TRUNCATE risk_check_definitions;
            TRUNCATE checkout_sessions;
            ALTER TABLE merchants AUTO_INCREMENT = 1;
            ALTER TABLE merchants_debtors AUTO_INCREMENT = 1;
            ALTER TABLE orders AUTO_INCREMENT = 1;
            SET FOREIGN_KEY_CHECKS = 1;
        ');
    }

    /**
     * @Given I have a(n) :state order with amounts :gross/:net/:tax, duration :duration and comment :comment
     */
    public function iHaveAnOrderWithoutExternalCode($state, $gross, $net, $tax, $duration, $comment)
    {
        $this->iHaveAnOrder($state, null, $gross, $net, $tax, $duration, $comment);
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
            ->setDataHash('829d100ebf4264d17fe53049a477efb2')
            ->setAddressId($debtorAddress->getId());
        $this->getDebtorExternalDataRepository()->insert($debtor);

        $merchantDebtor = (new MerchantDebtorEntity())
            ->setMerchantId($this->merchant->getId())
            ->setDebtorId(1)
            ->setUuid(self::DEBTOR_UUID)
            ->setPaymentDebtorId('test')
            ->setCreatedAt(new \DateTime('2019-01-01 12:00:00'))
            ->setIsWhitelisted(false)
        ;
        $this->getMerchantDebtorRepository()->insert($merchantDebtor);

        $financialDetails = (new MerchantDebtorFinancialDetailsEntity())
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setFinancingLimit(2000)
            ->setFinancingPower(1000)
            ->setCreatedAt(new DateTime())
        ;
        $this->getMerchantDebtorFinancialDetailsRepository()->insert($financialDetails);

        $order = (new OrderEntity())
            ->setExternalCode($externalCode)
            ->setState($state)
            ->setAmountNet($net)
            ->setAmountGross($gross)
            ->setAmountForgiven(0)
            ->setAmountTax($tax)
            ->setDuration($duration)
            ->setDebtorPersonId($person->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtor->getId())
            ->setExternalComment($comment)
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setMerchantId('1')
            ->setPaymentId('test')
            ->setCreatedAt(new \DateTime('2019-05-20 13:00:00'))
            ->setCheckoutSessionId(1)
            ->setUuid('test123');

        $this->iHaveASessionId("123123", 0);

        $this->getOrderRepository()->insert($order);
    }

    /**
     * @Given I have a invalid checkout_session_id :arg1
     */
    public function iHaveAInvalidSessionId($arg1)
    {
        $this->iHaveASessionId($arg1, false);
    }

    /**
     * @Given I have a checkout_session_id :arg1
     */
    public function iHaveASessionId($arg1, $active = true)
    {
        $checkoutSession = new CheckoutSessionEntity();
        $checkoutSession->setMerchantId(1)
            ->setMerchantDebtorExternalId($arg1)
            ->setUuid("123123")
            ->setIsActive($active)
            ->setCreatedAt(new DateTime('now'))
            ->setUpdatedAt(new DateTime('now'));
        $this->getCheckoutSessionRepository()->create($checkoutSession);
    }

    /**
     * @Given Order :externalCode was shipped at :date
     */
    public function orderWasShipped($externalCode, $date)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($externalCode, 1);
        $order->setShippedAt(new DateTime($date));
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
     * @Given the order :orderId has risk check :check failed
     */
    public function orderRiskCheckHasFailed($orderId, $check)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }

        $check = $this->getOrderRiskCheckRepositoryInterface()->findByOrderAndCheckName($order->getId(), $check);
        if ($check->isPassed()) {
            throw new RuntimeException("Risk check {$check} has not failed");
        }
    }

    /**
     * @param $orderId
     * @param $hash
     * @Given the order :orderId has the same hash :hash
     */
    public function orderHasTheSameHash($orderId, $hash)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }

        $debtorExternalData = $this->getDebtorExternalDataRepository()->getOneById($order->getDebtorExternalDataId());
        if ($debtorExternalData === null) {
            throw new RuntimeException('Debtor External Data not found');
        }

        if ($debtorExternalData->getDataHash() == '') {
            throw new RuntimeException('The hash is not generated!');
        }

        if ($debtorExternalData->getDataHash() !== md5($hash)) {
            throw new RuntimeException(sprintf("The generated hash is: %s, but %s was expected.", $debtorExternalData->getDataHash(), md5($hash)));
        }
    }

    /**
     * @Given the merchant debtor :externalId with merchantId :merchantId should be whitelisted
     */
    public function checkMerchantDebtorWhitelistStatus(string $externalId, string $merchantId)
    {
        $merchantDebtor = $this->getMerchantDebtorRepository()->getOneByExternalIdAndMerchantId($externalId, $merchantId, []);

        if (!$merchantDebtor->isWhitelisted()) {
            throw new RuntimeException(sprintf(
                'MerchantDebtor with id %s is not whitelisted.',
                $merchantDebtor->getId()
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
            ->prepare("UPDATE orders SET uuid = :uuid WHERE external_code = :orderExternalCode", [])
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
     * @Given the default risk check setting should be created with :jsonResponseFromMerchant
     * @param string $jsonResponseFromMerchant
     */
    public function checkMerchantHashTheDefaultRiskCheckSettingsCreated(string $jsonResponseFromMerchant)
    {
        $merchantResponse = json_decode($jsonResponseFromMerchant);

        $pdoQuery = $this->getConnection()
            ->prepare("select (select count(*) from merchant_risk_check_settings where merchant_id = :merchant_id) = (select count(*) from risk_check_definitions where name != 'debtor_address') as merchant_has_risk_settings", []);
        $pdoQuery->execute(['merchant_id' => $merchantResponse['id']]);
        $results = $pdoQuery->fetch(PDO::FETCH_ASSOC);

        Assert::eq($results['merchant_has_risk_settings'], 0, 'The default merchant risk settings did not match with the number of risk definitions');
    }

    /**
     * @Given order_identifications table should have a new record with:
     */
    public function orderIdentificationsTableShouldHaveANewRecordWith(TableNode $table)
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

    /**
     * @Given The following risk check results exist for order :orderExternalCode:
     */
    public function theFollowingRiskCheckResultsExistForOrderCO123(string $orderExternalCode, TableNode $table)
    {
        $order = $this->getOrder($orderExternalCode);

        foreach ($table as $row) {
            $riskCheckDefinition = $this->getRiskCheckDefinitionRepository()->getByName($row['check_name']);

            $this->getOrderRiskCheckRepositoryInterface()->insert(
                (new OrderRiskCheckEntity())
                    ->setOrderId($order->getId())
                    ->setRiskCheckDefinition($riskCheckDefinition)
                    ->setIsPassed($row['is_passed'] === '1' ? true : false)
            );
        }
    }

    private function getOrder($orderExternalCode): OrderEntity
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderExternalCode, $this->merchant->getId());

        if ($order === null) {
            throw new RuntimeException('Order not found');
        }

        return $order;
    }

    /**
     * @Given /^a merchant user exists$/
     */
    public function aMerchantUserExists()
    {
        $this->getMerchantUserRepository()->create(
            (new MerchantUserEntity())
                ->setUserId('oauthUserId')
                ->setMerchantId(1)
                ->setRoles(['ROLE_USER'])
        );
    }

    /**
     * @Given /^a merchant exists with company ID (\d+)$/
     */
    public function aMerchantExistsWithCompanyID($companyId)
    {
        $this->getMerchantRepository()->insert(
            (new MerchantEntity())
                ->setName('test merchant')
                ->setIsActive(true)
                ->setRoles(['ROLE_NOTHING'])
                ->setPaymentMerchantId('any-payment-id')
                ->setAvailableFinancingLimit(10000)
                ->setApiKey('testMerchantApiKey')
                ->setCompanyId((int) $companyId)
                ->setOauthClientId('testMerchantOauthClientId')
        );
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

    private function getMerchantDebtorFinancialDetailsRepository(): MerchantDebtorFinancialDetailsRepositoryInterface
    {
        return $this->get(MerchantDebtorFinancialDetailsRepositoryInterface::class);
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

    private function getOrderRiskCheckRepositoryInterface(): OrderRiskCheckRepositoryInterface
    {
        return $this->get(OrderRiskCheckRepositoryInterface::class);
    }

    private function getMerchantUserRepository(): MerchantUserRepositoryInterface
    {
        return $this->get(MerchantUserRepositoryInterface::class);
    }

    private function getCheckoutSessionRepository(): CheckoutSessionRepositoryInterface
    {
        return $this->get(CheckoutSessionRepositoryInterface::class);
    }

    private function getConnection(): PdoConnection
    {
        return $this->connection;
    }

    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
