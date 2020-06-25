<?php

namespace App\Console\Command;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MerchantLimitSlackNotificationCommand extends Command implements LoggingInterface, SlackClientAwareInterface
{
    use LoggingTrait, SlackClientAwareTrait;

    private const NAME = 'paella:merchant:notify-limit-drop';

    private const DESCRIPTION = 'Gives a list of merchants in slack that have lower percentage of power_amount.';

    /**
     * @var MerchantRepositoryInterface
     */
    private $merchantRepository;

    /**
     * @var SlackMessageFactory
     */
    private $slackMessageFactory;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        SlackMessageFactory $slackMessageFactory
    ) {
        parent::__construct();

        $this->merchantRepository = $merchantRepository;
        $this->slackMessageFactory = $slackMessageFactory;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->addOption('threshold', 't', InputOption::VALUE_OPTIONAL, 'The percentage below of which the notification will be triggered', 25);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('threshold');

        $output->writeln('Gathering merchants...');
        $messageBody = '';
        $merchants = $this->merchantRepository->findActiveWithFinancingPowerBelowPercentage($limit);

        if (!$merchants) {
            $output->writeln('No merchants with low power_amount found.');

            return 0;
        }

        foreach ($merchants as $merchant) {
            $messageBody .= "Merchant with id({$merchant->getId()}), and financing_power ({$merchant->getFinancingPower()}). \n";
        }

        $message = $this->slackMessageFactory->createSimpleWithServiceInfo('List of merchants that have financing_power less than '.$limit.'%.', $messageBody);
        $this->getSlackClient()->sendMessage($message);

        $output->writeln('DONE.');

        return 0;
    }
}
