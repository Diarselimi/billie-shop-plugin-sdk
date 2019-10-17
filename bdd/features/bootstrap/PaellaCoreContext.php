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
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsEntity;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsEntity;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Helper\Uuid\DummyUuidGenerator;
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

    public const DEBTOR_UUID = 'ad74bbc4-509e-47d5-9b50-a0320ce3d715';

    public const DEBTOR_COMPANY_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb70';

    public function __construct(KernelInterface $kernel, PdoConnection $connection)
    {
        $this->kernel = $kernel;
        $this->connection = $connection;

        $this->getConnection()->exec('
            SET SESSION wait_timeout=45;
            SET SESSION max_connections = 1000;
            SET SESSION max_allowed_packet = "16M";
        ');
        $this->cleanUpScenario();
        $this->initScenario();
    }

    public function initScenario()
    {
        $this->merchant = (new MerchantEntity())
            ->setName('Behat User')
            ->setIsActive(true)
            ->setPaymentMerchantId('f2ec4d5e-79f4-40d6-b411-31174b6519ac')
            ->setFinancingLimit(10000)
            ->setFinancingPower(10000)
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
            TRUNCATE order_financial_details;
            TRUNCATE order_line_items;
            TRUNCATE orders;
            TRUNCATE order_notifications;
            TRUNCATE persons;
            TRUNCATE debtor_external_data;
            TRUNCATE checkout_sessions;
            TRUNCATE addresses;
            TRUNCATE merchants_debtors;
            TRUNCATE merchant_settings;
            TRUNCATE merchant_risk_check_settings;
            TRUNCATE merchant_notification_settings;
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
     * @Given I have default limits and no order created yet
     */
    public function iHaveADebtorWithoutOrders()
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
            ->setCity('testCity')
            ->setCountry('TE');
        $this->getAddressRepository()->insert($debtorAddress);

        $debtor = (new DebtorExternalDataEntity())
            ->setName('test')
            ->setLegalForm('test')
            ->setIndustrySector('test')
            ->setSubindustrySector('test')
            ->setEstablishedCustomer(true)
            ->setBillingAddressId($deliveryAddress->getId())
            ->setMerchantExternalId('ext_id')
            ->setDataHash('829d100ebf4264d17fe53049a477efb2')
            ->setAddressId($debtorAddress->getId());
        $this->getDebtorExternalDataRepository()->insert($debtor);

        $merchantDebtor = (new MerchantDebtorEntity())
            ->setMerchantId($this->merchant->getId())
            ->setDebtorId(1)
            ->setCompanyUuid(self::DEBTOR_COMPANY_UUID)
            ->setUuid(self::DEBTOR_UUID)
            ->setPaymentDebtorId('test')
            ->setCreatedAt(new \DateTime('2019-01-01 12:00:00'))
            ->setIsWhitelisted(false);
        $this->getMerchantDebtorRepository()->insert($merchantDebtor);

        $financialDetails = (new MerchantDebtorFinancialDetailsEntity())
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setFinancingLimit(2000)
            ->setFinancingPower(1000)
            ->setCreatedAt(new DateTime());
        $this->getMerchantDebtorFinancialDetailsRepository()->insert($financialDetails);

        return [$person, $deliveryAddress, $debtor, $merchantDebtor];
    }

    /**
     * @Given I have a(n) :state order :externalCode with amounts :gross/:net/:tax, duration :duration and comment :comment
     */
    public function iHaveAnOrder($state, $externalCode, $gross, $net, $tax, $duration, $comment)
    {
        list($person, $deliveryAddress, $debtor, $merchantDebtor) = $this->iHaveADebtorWithoutOrders();

        $order = (new OrderEntity())
            ->setExternalCode($externalCode)
            ->setState($state)
            ->setAmountForgiven(0)
            ->setDebtorPersonId($person->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtor->getId())
            ->setExternalComment($comment)
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setMerchantId('1')
            ->setPaymentId(DummyUuidGenerator::DUMMY_UUID4)
            ->setCreatedAt(new \DateTime('2019-05-20 13:00:00'))
            ->setCheckoutSessionId(1)
            ->setUuid('test-order-uuid');

        $this->iHaveASessionId("123123", 0);

        $this->getOrderRepository()->insert($order);

        $this->getOrderFinancialDetailsRepository()->insert(
            (new OrderFinancialDetailsEntity())
                ->setOrderId($order->getId())
                ->setAmountGross($gross)
                ->setAmountNet($net)
                ->setAmountTax($tax)
                ->setDuration($duration)
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime())
        );
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
     * @Given the checkout_session_id :sessionId should be valid
     */
    public function theCheckoutSessionIdShouldBeValid($sessionId)
    {
        $sessionEntity = $this->getCheckoutSessionRepository()->findOneByUuid($sessionId);
        Assert::notNull($sessionEntity);
        Assert::true($sessionEntity->isActive());
    }

    /**
     * @Given the checkout_session_id :sessionId should be invalid
     */
    public function theCheckoutSessionIdShouldBeInValid($sessionId)
    {
        $sessionEntity = $this->getCheckoutSessionRepository()->findOneByUuid($sessionId);
        Assert::notNull($sessionEntity);
        Assert::false($sessionEntity->isActive());
    }

    /**
     * @Given the order :orderId has risk check :check failed
     */
    public function orderRiskCheckHasFailed($orderId, $check)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        Assert::notNull($order);

        $check = $this->getOrderRiskCheckRepositoryInterface()->findByOrderAndCheckName($order->getId(), $check);
        Assert::false($check->isPassed());
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
     * @Given the order :orderId :key is :expectedValue
     */
    public function orderDurationIs($orderId, $key, $expectedValue)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);

        Assert::notNull($order);

        $orderFinancialDetails = $this->getOrderFinancialDetailsRepository()->getCurrentByOrderId($order->getId());

        Assert::notNull($orderFinancialDetails);

        $actualValue = (in_array($key, ['amountGross', 'amountNet', 'amountTax', 'duration']))
            ? $orderFinancialDetails->{'get' . ucfirst($key)}() : $order->{'get' . ucfirst($key)}();

        Assert::eq($actualValue, $expectedValue);
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
     * @Given the default risk check setting should be created for merchant with company ID :companyId
     */
    public function checkMerchantHashTheDefaultRiskCheckSettingsCreated($companyId)
    {
        $merchant = $this->getMerchantRepository()->getOneByCompanyId((int) $companyId);

        Assert::notNull($merchant);

        $pdoQuery = $this->getConnection()
            ->prepare("select (select count(*) from merchant_risk_check_settings where merchant_id = :merchant_id) = (select count(*) from risk_check_definitions where name != 'debtor_address') as merchant_has_risk_settings", []);
        $pdoQuery->execute(['merchant_id' => $merchant->getId()]);
        $results = $pdoQuery->fetch(PDO::FETCH_ASSOC);

        Assert::eq($results['merchant_has_risk_settings'], 1);
    }

    /**
     * @Given the default notification settings should be created for merchant with company ID :companyId
     */
    public function theDefaultNotificationSettingsShouldBeCreatedFor($companyId)
    {
        $merchant = $this->getMerchantRepository()->getOneByCompanyId((int) $companyId);

        Assert::notNull($merchant);

        foreach (MerchantNotificationSettingsFactory::DEFAULT_SETTINGS as $notificationType => $enabled) {
            $setting = $this
                ->getMerchantNotificationSettingsRepository()
                ->getByMerchantIdAndNotificationType($merchant->getId(), $notificationType)
            ;

            Assert::notNull($setting);
            Assert::eq($setting->isEnabled(), $enabled);
        }
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
     * @Given a merchant user exists with role :role
     */
    public function aMerchantUserExists(string $role)
    {
        $this->getMerchantUserRepository()->create(
            (new MerchantUserEntity())
                ->setUserId('oauthUserId')
                ->setMerchantId(1)
                ->setRoles([$role])
                ->setFirstName('test')
                ->setLastName('test')
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
                ->setPaymentMerchantId('any-payment-id')
                ->setFinancingPower(10000)
                ->setFinancingLimit(10000)
                ->setApiKey('testMerchantApiKey')
                ->setCompanyId((int) $companyId)
                ->setOauthClientId('testMerchantOauthClientId')
        );
    }

    /**
     * @Given merchant user with merchant id :merchantId and user id :userId should be created
     */
    public function merchantUserWithMerchantIdAndUserIdShouldBeCreated(string $merchantId, string $userId)
    {
        $user = $this->getMerchantUserRepository()->getOneByUserId($userId);

        Assert::notNull($user);
        Assert::eq($user->getMerchantId(), $merchantId);
        Assert::eq($user->getUserId(), $userId);
    }

    /**
     * @Given merchant debtor has financing power :power
     */
    public function merchantDebtorHasFinancingPower(string $power)
    {
        $merchantDebtor = $this->getMerchantDebtorFinancialDetailsRepository()->getCurrentByMerchantDebtorId(1);

        Assert::notNull($merchantDebtor);
        Assert::eq($merchantDebtor->getFinancingPower(), $power);
    }

    /**
     * @Given The following notification settings exist for merchant :merchantId:
     */
    public function theFollowingNotificationSettingsExistForMerchant1($merchantId, TableNode $table)
    {
        foreach ($table as $row) {
            $this->getMerchantNotificationSettingsRepository()->insert(
                (new MerchantNotificationSettingsEntity())
                    ->setMerchantId((int) $merchantId)
                    ->setNotificationType($row['notification_type'])
                    ->setEnabled($row['enabled'] === '1')
            );
        }
    }

    /**
     * @Given Order notification should exist for order :orderCode with type :notificationType
     */
    public function orderNotificationShouldExistForOrderWithType($orderCode, $notificationType)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderCode, $this->merchant->getId());

        $notification = $this
            ->getOrderNotificationRepository()
            ->getOneByOrderIdAndNotificationType($order->getId(), $notificationType)
        ;

        Assert::notNull($notification);
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

    private function getOrderFinancialDetailsRepository(): OrderFinancialDetailsRepositoryInterface
    {
        return $this->get(OrderFinancialDetailsRepositoryInterface::class);
    }

    private function getMerchantNotificationSettingsRepository(): MerchantNotificationSettingsRepositoryInterface
    {
        return $this->get(MerchantNotificationSettingsRepositoryInterface::class);
    }

    private function getOrderNotificationRepository(): OrderNotificationRepositoryInterface
    {
        return $this->get(OrderNotificationRepositoryInterface::class);
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
