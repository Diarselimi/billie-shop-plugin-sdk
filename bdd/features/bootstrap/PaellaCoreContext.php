<?php

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\DebtorSettings\DebtorSettingsRepositoryInterface;
use App\DomainModel\FraudRules\FraudRuleEntity;
use App\DomainModel\FraudRules\FraudRuleRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntity;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentRepositoryInterface;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsEntity;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingPersistenceService;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsEntity;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTO;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
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
use App\Infrastructure\Repository\DebtorExternalDataRepository;
use App\Infrastructure\Repository\DebtorInformationChangeRequestRepository;
use App\Infrastructure\Repository\MerchantDebtorRepository;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Ozean12\Money\Money;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class PaellaCoreContext extends MinkContext
{
    use KernelDictionary;

    public const TEST_MERCHANT_OAUTH_CLIENT_ID = 'testMerchantOauthClientId';

    public const DUMMY_UUID4 = '6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4';

    public const DEBTOR_UUID = 'ad74bbc4-509e-47d5-9b50-a0320ce3d715';

    public const DEBTOR_COMPANY_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb70';

    public const SANDBOX_MERCHANT_PAYMENT_UUID = '1ac823bd-2a3e-48b0-aa61-3b95962922eb';

    public const MERCHANT_COMPANY_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb70';

    private $connection;

    /**
     * @var MerchantEntity
     */
    private $merchant;

    /** @var MerchantUserEntity|null */
    private $lastCreatedUser;

    private $debtorData;

    private $resetDbSql;

    public function __construct(KernelInterface $kernel, PdoConnection $connection)
    {
        $this->kernel = $kernel;
        $this->connection = $connection;

        $this->prepareResetDbQueries();
    }

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->initMinkExtension();
        $this->cleanUpDatabase();
        $this->createInitialDataset();
    }

    /**
     * @AfterScenario
     */
    private function releaseResources()
    {
        $this->connection->disconnect();
    }

    private function cleanUpDatabase()
    {
        $this->connection->exec($this->resetDbSql);
        $this->merchant = null;
    }

    /**
     * @see https://github.com/Behat/Symfony2Extension/pull/124
     */
    private function initMinkExtension()
    {
        $this->getMink()->registerSession(
            'test',
            new Session(new BrowserKitDriver(new HttpKernelBrowser($this->kernel)))
        );
        $this->getMink()->setDefaultSessionName('test');
    }

    private function createInitialDataset()
    {
        $fraudRule = (new FraudRuleEntity())
            ->setIncludedWords(['Download', 'ESD'])
            ->setExcludedWords(['Lizenz'])
            ->setCheckForPublicDomain(true);

        $this->getFraudCheckRulesRepository()->insert($fraudRule);
        $this->merchant = $this->createMerchant();
    }

    private function createMerchant(): MerchantEntity
    {
        $now = new \DateTime();
        $merchant = (new MerchantEntity())
            ->setName('Behat Merchant')
            ->setCompanyUuid(self::MERCHANT_COMPANY_UUID)
            ->setIsActive(true)
            ->setPaymentUuid('f2ec4d5e-79f4-40d6-b411-31174b6519ac')
            ->setFinancingLimit(new Money(10000))
            ->setSepaB2BDocumentUuid('c7be46c0-e049-4312-b274-258ec5aeeb70')
            ->setFinancingPower(new Money(10000))
            ->setApiKey('test')
            ->setCompanyId('10')
            ->setOauthClientId('oauthClientId');

        $this->getMerchantRepository()->insert($merchant);

        $scoreThreshold = (new ScoreThresholdsConfigurationEntity())
            ->setCrefoLowScoreThreshold(350)
            ->setCrefoHighScoreThreshold(400)
            ->setSchufaLowScoreThreshold(200)
            ->setSchufaAverageScoreThreshold(220)
            ->setSchufaHighScoreThreshold(260)
            ->setSchufaSoleTraderScoreThreshold(235)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
        $this->getScoreThresholdsConfigurationRepository()->insert($scoreThreshold);

        $this->getMerchantSettingsRepository()->insert(
            (new MerchantSettingsEntity())
                ->setMerchantId($merchant->getId())
                ->setInitialDebtorFinancingLimit(10000)
                ->setDebtorFinancingLimit(10000)
                ->setMinOrderAmount(0)
                ->setScoreThresholdsConfigurationId($scoreThreshold->getId())
                ->setUseExperimentalDebtorIdentification(false)
                ->setDebtorForgivenessThreshold(1.0)
                ->setInvoiceHandlingStrategy('http')
                ->setCreatedAt($now)
                ->setUpdatedAt($now)
        );

        $this->getMerchantOnboardingPersistenceService()->createWithSteps($merchant->getId());

        return $merchant;
    }

    private function prepareResetDbQueries()
    {
        $sqls = [
            'SET FOREIGN_KEY_CHECKS = 0;',
        ];

        $tables = $this->connection->query(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_TYPE="BASE TABLE" 
                AND TABLE_NAME NOT IN ("phinxlog", "public_domains");'
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($tables as $table) {
            $sqls[] = "TRUNCATE TABLE {$table['TABLE_NAME']};";
            $sqls[] = "ALTER TABLE {$table['TABLE_NAME']} AUTO_INCREMENT = 1;";
        }

        $sqls[] = 'SET FOREIGN_KEY_CHECKS = 1;';

        $this->resetDbSql = implode(PHP_EOL, $sqls);
    }

    /**
     * @Given the order :orderId belongs to company :companyUuid
     */
    public function orderBelongsToCompany($orderId, $companyUuid)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, $this->merchant->getId());
        $merchantDebtor = $this->getMerchantDebtorRepository()->getOneById($order->getMerchantDebtorId());
        Assert::eq($merchantDebtor->getCompanyUuid(), $companyUuid);
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
            ->setCreatedAt(new \DateTime('2019-01-01 12:00:00'));
        $this->getMerchantDebtorRepository()->insert($merchantDebtor);

        $debtorSettings = (new DebtorSettingsEntity())
            ->setCompanyUuid($merchantDebtor->getCompanyUuid())
            ->setIsWhitelisted(true);
        $this->getDebtorSettingsRepository()->insert($debtorSettings);

        return $this->debtorData = [$person, $deliveryAddress, $debtor, $merchantDebtor];
    }

    /**
     * @Given I have a(n) :state order :externalCode with amounts :gross/:net/:tax, duration :duration and comment :comment
     */
    public function iHaveAnOrder($state, $externalCode, $gross, $net, $tax, $duration, $comment, $paymentUuid = null)
    {
        [$person, $deliveryAddress, $debtor, $merchantDebtor] = $this->debtorData ?? $this->iHaveADebtorWithoutOrders();

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
            ->setPaymentId($paymentUuid ?? self::DUMMY_UUID4)
            ->setCreatedAt(new \DateTime('2019-05-20 13:00:00'))
            ->setCheckoutSessionId(1)
            ->setUuid('test-order-uuid' . $externalCode)
            ->setCompanyBillingAddressUuid('c7be46c0-e049-4312-b274-258ec5aeeb71');

        $this->iHaveASessionId("123123" . $externalCode, 0);

        $this->getOrderRepository()->insert($order);

        $this->getOrderFinancialDetailsRepository()->insert(
            (new OrderFinancialDetailsEntity())
                ->setOrderId($order->getId())
                ->setAmountGross(new Money($gross))
                ->setAmountNet(new Money($net))
                ->setAmountTax(new Money($tax))
                ->setDuration($duration)
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime())
        );
    }

    /**
     * @Given The merchant have sandbox credentials created
     */
    public function iHaveTheMerchantWithSandboxCredentialsCreated()
    {
        $merchant = (new MerchantEntity())
            ->setId(1)
            ->setSandboxPaymentUuid(self::SANDBOX_MERCHANT_PAYMENT_UUID)
            ->setFinancingPower(new Money(1111))
            ->setFinancingLimit(new Money(22222))
            ->setUpdatedAt(new \DateTime())
            ;
        $this->getMerchantRepository()->update($merchant);
    }

    /**
     * @Given I have a invalid checkout_session_id :arg1
     */
    public function iHaveAInvalidSessionId($sessionId)
    {
        $this->iHaveASessionId($sessionId, false);
    }

    /**
     * @Given I have a checkout_session_id :arg1
     */
    public function iHaveASessionId($sessionId, $active = true)
    {
        $checkoutSession = new CheckoutSessionEntity();
        $checkoutSession->setMerchantId(1)
            ->setMerchantDebtorExternalId($sessionId)
            ->setUuid($sessionId)
            ->setIsActive($active)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
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
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, $this->merchant->getId());
        if ($order === null) {
            if ($state === 'null') {
                return;
            }

            throw new RuntimeException('Order not found by Behat in ' . __METHOD__ . ':' . __LINE__);
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
     * @Given the order :orderId has invoice data
     */
    public function orderHasInvoiceData($orderId)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderId, 1);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }
        if (!$order->getInvoiceNumber()) {
            throw new RuntimeException(sprintf(
                'Order %s should have invoice data',
                $order->getId()
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
     * @Given the debtor company with uuid :companyUuid should be whitelisted
     */
    public function checkDebtorSettingsWhitelistStatus(string $companyUuid)
    {
        $debtorSettings = $this->getDebtorSettingsRepository()->getOneByCompanyUuid($companyUuid);

        if (!$debtorSettings->isWhitelisted()) {
            throw new RuntimeException(sprintf(
                'DebtorSettings for companyUuid %s is not whitelisted.',
                $debtorSettings->getCompanyUuid()
            ));
        }
    }

    /**
     * @Given the merchant debtor with companyUuid :companyUuid should have all change requests seen
     */
    public function checkDebtorChangeRequestSeenAlready(string $companyUuid)
    {
        $debtorChangeRequest = $this->getDebtorInformationChangeRequestRepository()->getNotSeenRequestByCompanyUuid($companyUuid);

        Assert::null($debtorChangeRequest);
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
                ->getByMerchantIdAndNotificationType($merchant->getId(), $notificationType);

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
     * @Given a merchant user exists with role :role and permission :permission
     */
    public function aMerchantUserExistsWithRoleAndPermission(string $roleName, string $permission)
    {
        $role = $this->createRole($roleName, $roleName . '_uuid', [$permission]);
        $this->createUser($role->getId());
    }

    /**
     * @Given a merchant user exists with permission :permission
     */
    public function aMerchantUserExistsWithPermission(string $permission)
    {
        $role = $this->createRole('test', 'test_uuid', [$permission]);
        $this->createUser($role->getId());
    }

    /**
     * @Given a merchant user exists with a role with permission :permission and overridden permission :overridden
     */
    public function aMerchantUserExistsWithRoleAndCustomPermission($rolePermission, $overriddenPermission)
    {
        $role = $this->createRole('test', 'test_uuid', [$rolePermission]);
        $this->createUser($role->getId(), [$overriddenPermission]);
    }

    /**
     * @Given a merchant user exists with overridden permission :permission
     */
    public function aMerchantUserExistsWithOverridenPermission(string $permission)
    {
        $role = $this->createRole('test', 'test_uuid', ['NOTHING']);
        $this->createUser($role->getId(), [$permission]);
    }

    /**
     * @Given I have the onboarding for merchant :merchantId with status :status
     */
    public function iHaveTheOnboardingForMerchantWithStatus(string $merchantId, string $status)
    {
        $merchantOnboard = (new MerchantOnboardingEntity())
            ->setState($status)
            ->setUuid(self::DUMMY_UUID4)
            ->setMerchantId((int) $merchantId);

        $this->getMerchantOnboardingRepository()->insert($merchantOnboard);

        $transition = (new MerchantOnboardingTransitionEntity())
            ->setReferenceId($merchantOnboard->getId())
            ->setFrom('new')
            ->setTo('complete')
            ->setTransition('complete')
            ->setTransitedAt(new \DateTime())
        ;
        $this->getMerchantOnboardingTransitionRepository()->insert($transition);
    }

    /**
     * @Given a merchant user exists with uuid :userUuid, role ID :role and :invitationStatus invitation
     */
    public function aMerchantUserExistsWithUuidAndInvitation($userUuid, $roleId, $invitationStatus)
    {
        $role = (new MerchantUserRoleEntity())
            ->setId($roleId)
            ->setName('test')
            ->setUuid('test_uuid')
            ->setMerchantId(1)
            ->setPermissions(['TEST']);
        $this->createUser($role->getId(), [], $userUuid, $invitationStatus);
    }

    /**
     * @Given an invitation exists for email :email, role ID :role and :invitationStatus invitation
     */
    public function anInvitationExistsWithData($email, $roleId, $invitationStatus)
    {
        $this->createInvitation($email, 1, $roleId, null, $invitationStatus);
    }

    /**
     * @Given an invitation with uuid :uuid and status :status exists for email :email and role ID :role
     */
    public function anInvitationExistWithUuidStatusAndData($uuid, $invitationStatus, $email, $roleId)
    {
        $this->createInvitation($email, 1, $roleId, null, $invitationStatus, $uuid);
    }

    /**
     * @Given a complete invitation with uuid :uuid exists for user :userId, email :email and role ID :role
     */
    public function aCompleteInvitationExistWithUuidAndData($uuid, $userId, $email, $roleId)
    {
        $this->createInvitation($email, 1, $roleId, $userId, 'complete', $uuid);
    }

    /**
     * @Given an invitation with token :token and status :status exists for email :email and role ID :role
     */
    public function anInvitationExistWithTokenStatusAndData($token, $invitationStatus, $email, $roleId)
    {
        $this->createInvitation($email, 1, $roleId, null, $invitationStatus, null, $token);
    }

    private function createUser(
        int $roleId,
        array $userPermissions = [],
        $userUuid = 'oauthUserId',
        string $invitationStatus = 'complete'
    ): MerchantUserEntity {
        $user = (new MerchantUserEntity())
            ->setUuid($userUuid)
            ->setMerchantId(1)
            ->setPermissions($userPermissions)
            ->setRoleId($roleId)
            ->setFirstName('test')
            ->setLastName('test');
        $this->getMerchantUserRepository()->create($user);
        $this->createInvitation('test@billie.dev', 1, $roleId, $user->getId(), $invitationStatus);

        $this->lastCreatedUser = $user;

        return $user;
    }

    /**
     * @Given /^a merchant exists with company ID (\d+)$/
     */
    public function aMerchantExistsWithCompanyID($companyId)
    {
        $merchant = (new MerchantEntity())
            ->setName('test merchant')
            ->setIsActive(true)
            ->setPaymentUuid('any-payment-id')
            ->setFinancingPower(new Money(10000))
            ->setFinancingLimit(new Money(10000))
            ->setApiKey('testMerchantApiKey')
            ->setCompanyId((int) $companyId)
            ->setCompanyUuid(self::MERCHANT_COMPANY_UUID)
            ->setOauthClientId(self::TEST_MERCHANT_OAUTH_CLIENT_ID);
        $this->getMerchantRepository()->insert($merchant);

        return $merchant;
    }

    /**
     * @Given merchant user with merchant id :merchantId and user id :userId should be created
     */
    public function merchantUserWithMerchantIdAndUserIdShouldBeCreated(string $merchantId, string $userId)
    {
        $user = $this->getMerchantUserRepository()->getOneByUuid($userId);

        Assert::notNull($user);
        Assert::eq($user->getMerchantId(), $merchantId);
        Assert::eq($user->getUuid(), $userId);
    }

    /**
     * @Given I have the following Financial Assessment Data:
     */
    public function iHaveTheFollowingFinancialAssessmentData(PyStringNode $node): void
    {
        $now = (new \DateTime());
        $entity = (new MerchantFinancialAssessmentEntity())
            ->setMerchantId(1)
            ->setData(json_decode($node->getRaw(), true))
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->getMerchantFinancialAssessmentRepository()
            ->insert($entity);
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
            ->getOneByOrderIdAndNotificationType($order->getId(), $notificationType);

        Assert::notNull($notification);
    }

    /**
     * @Given Order notification should NOT exist for order :orderCode with type :notificationType
     */
    public function orderNotificationShouldNotExistForOrderWithType($orderCode, $notificationType)
    {
        $order = $this->getOrderRepository()->getOneByExternalCode($orderCode, $this->merchant->getId());

        $notification = $this
            ->getOrderNotificationRepository()
            ->getOneByOrderIdAndNotificationType($order->getId(), $notificationType);

        Assert::null($notification);
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

    private function getDebtorInformationChangeRequestRepository(): DebtorInformationChangeRequestRepositoryInterface
    {
        return $this->get(DebtorInformationChangeRequestRepositoryInterface::class);
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

    private function getMerchantUserRoleRepository(): MerchantUserRoleRepositoryInterface
    {
        return $this->get(MerchantUserRoleRepositoryInterface::class);
    }

    private function getMerchantUserInvitationRepository(): MerchantUserInvitationRepositoryInterface
    {
        return $this->get(MerchantUserInvitationRepositoryInterface::class);
    }

    private function getMerchantUserInvitationFactory(): MerchantUserInvitationEntityFactory
    {
        return $this->get(MerchantUserInvitationEntityFactory::class);
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

    private function getFraudCheckRulesRepository(): FraudRuleRepositoryInterface
    {
        return $this->get(FraudRuleRepositoryInterface::class);
    }

    private function getMerchantOnboardingRepository(): MerchantOnboardingRepositoryInterface
    {
        return $this->get(MerchantOnboardingRepositoryInterface::class);
    }

    private function getMerchantOnboardingTransitionRepository(): MerchantOnboardingTransitionRepositoryInterface
    {
        return $this->get(MerchantOnboardingTransitionRepositoryInterface::class);
    }

    private function getMerchantOnboardingStepRepository(): MerchantOnboardingStepRepositoryInterface
    {
        return $this->get(MerchantOnboardingStepRepositoryInterface::class);
    }

    private function getMerchantOnboardingPersistenceService(): MerchantOnboardingPersistenceService
    {
        return $this->get(MerchantOnboardingPersistenceService::class);
    }

    private function getMerchantFinancialAssessmentRepository(): MerchantFinancialAssessmentRepositoryInterface
    {
        return $this->get(MerchantFinancialAssessmentRepositoryInterface::class);
    }

    private function getDebtorSettingsRepository(): DebtorSettingsRepositoryInterface
    {
        return $this->get(DebtorSettingsRepositoryInterface::class);
    }

    private function getConnection(): PdoConnection
    {
        return $this->connection;
    }

    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * @Given I have a role of name :name with uuid :uuid and permissions
     */
    public function iHaveARoleOfNameWithUuidAndPermissions($name, $uuid, PyStringNode $permissions)
    {
        $this->createRole($name, $uuid, json_decode($permissions->__toString(), true));
    }

    /**
     * @Given /^all the default roles should be created for merchant with company ID (\d+)$/
     */
    public function theAllTheDefaultRolesShouldBeCreatedForMerchantWithCompanyID($companyId)
    {
        $merchant = $this->getMerchantRepository()->getOneByCompanyId((int) $companyId);

        Assert::notNull($merchant);

        $roles = $this->getMerchantUserRoleRepository()->findAllByMerchantId($merchant->getId());
        Assert::notEmpty($roles, 'Merchant has no defined roles');

        $roleNames = array_map(function (MerchantUserRoleEntity $role) {
            return $role->getName();
        }, iterator_to_array($roles));

        foreach (MerchantUserDefaultRoles::ROLES as $role) {
            if ($role['name'] == MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name']) {
                continue;
            }
            Assert::oneOf($role['name'], $roleNames, "Merchant {$merchant->getId()} has no role with name {$role['name']}");
        }
    }

    /**
     * @Given The following onboarding steps are in states for merchant :merchantPaymentUuid:
     */
    public function theFollowingOnboardingStepsAreInStates(TableNode $table, $merchantId)
    {
        foreach ($table as $row) {
            $step = $this->getMerchantOnboardingStepRepository()->getOneByStepNameAndPaymentUuid($row['name'], $merchantId);
            $step->setState($row['state']);
            $this->getMerchantOnboardingStepRepository()->update($step);
        }
    }

    /**
     * @Given the onboarding step :step should be in state :state for merchant :merchantPaymentUuid
     */
    public function theOnboardingStepShouldBeInState(string $step, string $state, int $merchantId)
    {
        $step = $this->getMerchantOnboardingStepRepository()->getOneByStepNameAndPaymentUuid($step, $merchantId);
        Assert::eq($step->getState(), $state, 'The onboarding step is in a different state');
    }

    public function createRole($name, $uuid, array $permissions): MerchantUserRoleEntity
    {
        $role = (new MerchantUserRoleEntity())
            ->setPermissions($permissions)
            ->setUuid($uuid)
            ->setMerchantId(1)
            ->setName($name);

        $this->getMerchantUserRoleRepository()->create($role);

        return $role;
    }

    public function createInvitation(
        string $email,
        int $merchantId,
        int $roleId,
        int $merchantUserId = null,
        string $invitationStatus = null,
        ?string $uuid = null,
        ?string $token = null
    ): MerchantUserInvitationEntity {
        $invitation = $this->getMerchantUserInvitationFactory()
            ->create($email, $merchantId, $roleId);

        $shouldRevoke = false;
        switch ($invitationStatus) {
            case 'revoked':
                $invitation->setExpiresAt(new \DateTime('+1 day'));
                $shouldRevoke = true;

                break;
            case MerchantInvitedUserDTO::INVITATION_STATUS_PENDING:
                $invitation->setExpiresAt(new \DateTime('+1 day'));

                break;
            case MerchantInvitedUserDTO::INVITATION_STATUS_EXPIRED:
                $invitation->setExpiresAt(new \DateTime('-1 day'));

                break;
            default:
                $invitation
                    ->setExpiresAt(new \DateTime('-1 second'))
                    ->setMerchantUserId($merchantUserId);
        }

        $invitation->setUuid($uuid ?: 'test_uuid-' . $email);

        if ($token !== null) {
            $invitation->setToken($token);
        }

        $this->getMerchantUserInvitationRepository()->create($invitation);

        if ($shouldRevoke) {
            $this->getMerchantUserInvitationRepository()
                ->revokeValidByEmailAndMerchant($invitation->getEmail(), $invitation->getMerchantId());
            $invitation->setRevokedAt(new \DateTime());
        }

        return $invitation;
    }

    /**
     * @Then /^the response status code should be "([^"]*)"$/
     */
    public function theResponseStatusCodeShouldBe($code)
    {
        $this->getMink()->assertSession()->statusCodeEquals((int) $code);
    }

    /**
     * @Given a user invitation with role :roleName and email :email should have been created for merchant with company ID :id
     */
    public function aUserInvitationForShouldBeCreatedForMerchantWithCompanyID($roleName, $email, $id)
    {
        $merchant = $this->getMerchantRepository()->getOneByCompanyId((int) $id);
        Assert::notNull($merchant, 'Merchant was null');

        $role = $this->getMerchantUserRoleRepository()->getOneByName($roleName, $merchant->getId());
        Assert::notNull($role, 'Role was null');

        $pdoQuery = $this->getConnection()->prepare(
            "SELECT COUNT(*) as total FROM merchant_user_invitations 
              WHERE merchant_id = :merchant_id AND email = :email AND merchant_user_role_id = :role_id",
            []
        );
        $pdoQuery->execute(['merchant_id' => $merchant->getId(), 'email' => $email, 'role_id' => $role->getId()]);
        $results = $pdoQuery->fetch(PDO::FETCH_ASSOC);

        Assert::eq($results['total'], 1);
    }

    /**
     * @Given a merchant exists with company ID :id and sandbox merchant payment UUID :uuid
     */
    public function aMerchantExistsWithCompanyIDAndSandboxMerchantPaymentUUID($id, $uuid)
    {
        $merchant = $this->getMerchantRepository()->getOneByCompanyId((int) $id);
        Assert::notNull($merchant);
        Assert::same($merchant->getSandboxPaymentUuid(), $uuid);
    }

    /**
     * @Given /^the sandbox merchant payment UUID is already set$/
     */
    public function theSandboxMerchantPaymentUUIDIsSet()
    {
        $this->merchant->setSandboxPaymentUuid(self::SANDBOX_MERCHANT_PAYMENT_UUID);
        $this->getMerchantRepository()->update($this->merchant);
    }

    /**
     * @Given /^the sepa mandate document should exist for merchant$/
     */
    public function theSepaMandateDocumentShouldExistForMerchant()
    {
        $merchant = $this->getMerchantRepository()->getOneById($this->merchant->getId());

        Assert::notNull($merchant->getSepaB2BDocumentUuid(), 'There is no file.');
    }

    /**
     * @Given The :check merchant risk check for merchant :merchantId is configured as enabled = :enabledValue and decline_on_failure = :declineValue
     */
    public function theMerchantRiskCheckForMerchantIsConfiguredAsEnabledAndDecline_on_failure(
        $check,
        $merchantId,
        $enabledValue,
        $declineValue
    ) {
        $riskCheckDefinition = $this->getRiskCheckDefinitionRepository()->getByName($check);
        Assert::isInstanceOf(
            $riskCheckDefinition,
            RiskCheckDefinitionEntity::class,
            "risk check {$check} not found for merchant {$merchantId}"
        );

        $this->getConnection()->exec(
            "UPDATE merchant_risk_check_settings SET enabled={$enabledValue}, decline_on_failure={$declineValue} WHERE id="
            . $riskCheckDefinition->getId()
        );
    }

    /**
     * @Given /^I have orders with the following data$/
     */
    public function iHaveOrdersWithTheFollowingData(TableNode $table)
    {
        foreach ($table as $row) {
            $this->iHaveAnOrder(
                $row['state'],
                $row['external_id'],
                $row['gross'],
                $row['net'],
                $row['tax'],
                $row['duration'],
                $row['comment'],
                $row['payment_uuid']
            );
        }
    }

    /**
     * @Given a merchant :merchantId is complete at :date
     */
    public function aMerchantIsCompleteAt(string $merchantId, string $date)
    {
        $onboarding = $this->getMerchantOnboardingRepository()->findNewestByPaymentUuid($merchantId);
        $onboarding->setState('complete');
        $this->getMerchantOnboardingRepository()->update($onboarding);

        $transition = (new MerchantOnboardingTransitionEntity())
            ->setReferenceId($onboarding->getId())
            ->setFrom('new')
            ->setTo('complete')
            ->setTransition('complete')
            ->setTransitedAt(new \DateTime($date))
        ;
        $this->getMerchantOnboardingTransitionRepository()->insert($transition);
    }

    /**
     * @Given the following debtor information change requests exist:
     */
    public function theFollowingDebtorInformationChangeRequestsExist(TableNode $table)
    {
        foreach ($table as $row) {
            $debtorInformationChangeRequest = (new DebtorInformationChangeRequestEntity())
                ->setUuid($row['uuid'])
                ->setMerchantUserId(1)
                ->setCompanyUuid($row['company_uuid'])
                ->setIsSeen($row['is_seen'])
                ->setName('Some name')
                ->setCity('Berlin')
                ->setPostalCode('10247')
                ->setStreet('Some street')
                ->setMerchantUserId(1)
                ->setState($row['state']);
            $this->getDebtorInformationChangeRequestRepository()->insert(
                $debtorInformationChangeRequest
            );
        }
    }

    /**
     * @Then change request number :number from debtor :debtorUuid should have :field :value
     */
    public function changeRequestNumberShouldHaveStatus(int $number, string $debtorUuid, string $field, string $value)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $query = $queryBuilder
            ->select('dicr.' . $field)
            ->from(DebtorInformationChangeRequestRepository::TABLE_NAME, 'dicr')
            ->innerJoin(
                'dicr',
                MerchantDebtorRepository::TABLE_NAME,
                'md',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'md.company_uuid',
                        'dicr.company_uuid'
                    ),
                    $queryBuilder->expr()->eq(
                        'md.uuid',
                        ':debtorUuid'
                    )
                )
            )
            ->orderBy('dicr.created_at', 'ASC');

        $pdoQuery = $this->getConnection()
            ->prepare($query->getSQL());
        $pdoQuery->execute(['debtorUuid' => $debtorUuid]);
        $rows = $pdoQuery->fetchAll();
        $index = $number - 1;
        Assert::eq($value, $rows[$index][$field]);
    }

    /**
     * @Then debtor information change request :uuid should have state :state
     */
    public function changeRequestShouldHaveState(string $uuid, string $state)
    {
        Assert::eq(
            $state,
            $this
                ->getDebtorInformationChangeRequestRepository()
                ->getOneByUuid($uuid)
                ->getState()
        );
    }

    /**
     * @Given the debtor has an information change request with state :state
     */
    public function theDebtorHasAnInformationChangeRequest(string $state)
    {
        $informationChangeRequest = (new DebtorInformationChangeRequestEntity())
            ->setUuid(uniqid())
            ->setCompanyUuid(self::DEBTOR_COMPANY_UUID)
            ->setName('Some name')
            ->setCity('Berlin')
            ->setPostalCode('10247')
            ->setStreet('Charlottenstraße')
            ->setMerchantUserId($this->lastCreatedUser->getId())
            ->setIsSeen(false)
            ->setState($state);
        $this->getDebtorInformationChangeRequestRepository()->insert(
            $informationChangeRequest
        );
    }

    /**
     * @Then the order with uuid :uuid should have amounts :gross/:net/:tax
     */
    public function theOrderShouldHaveAmount(string $uuid, float $gross, float $net, float $tax)
    {
        $order = $this->getOrderRepository()->getOneByUuid($uuid);
        $financialDetails = $this->getOrderFinancialDetailsRepository()->getCurrentByOrderId($order->getId());
        Assert::eq($financialDetails->getAmountGross()->getMoneyValue(), $gross);
        Assert::eq($financialDetails->getAmountNet()->getMoneyValue(), $net);
        Assert::eq($financialDetails->getAmountTax()->getMoneyValue(), $tax);
    }

    /**
     * @Given the following debtor external data exist:
     */
    public function theFollowingDebtorExternalDataExist(TableNode $table)
    {
        // first we need to add some mandatory data
        $this->iHaveAnOrder('new', 'CO123', 1000, 900, 100, 30, 'test order');

        foreach ($table as $row) {
            $debtorExternalData = (new DebtorExternalDataEntity())
                ->setId($row['id'])
                ->setName($row['name'])
                ->setLegalForm($row['legal_form'])
                ->setAddressId($row['address_id'])
                ->setBillingAddressId($row['billing_address_id'])
                ->setMerchantExternalId($row['merchant_external_id'])
                ->setDataHash($row['debtor_data_hash'])
                ;
            $this->getDebtorExternalDataRepository()->insert($debtorExternalData);
        }
    }

    /**
     * @Then debtor external data :id should have been invalidated :merchantExternalId
     */
    public function debtorExternalDataShouldHaveBeenInvalidated(int $id, string $merchantExternalId)
    {
        $debtorExternalData = $this->getDebtorExternalDataRepository()->getOneById($id);

        Assert::eq($debtorExternalData->getDataHash(), DebtorExternalDataRepository::INVALID_DEBTOR_DATA_HASH);
        Assert::eq($debtorExternalData->getMerchantExternalId(), $merchantExternalId ."-".DebtorExternalDataRepository::INVALID_MERCHANT_EXTERNAL_ID_SUFFIX);
    }
}
