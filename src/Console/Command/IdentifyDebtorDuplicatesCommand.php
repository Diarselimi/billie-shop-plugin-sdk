<?php

namespace App\Console\Command;

use App\DomainModel\MerchantDebtor\DebtorDuplicateFinder;
use App\DomainModel\MerchantDebtor\DebtorDuplicateHandler;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessage;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachment;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IdentifyDebtorDuplicatesCommand extends Command
{
    private const NAME = 'paella:identify-debtor-duplicates';

    private const DESCRIPTION = 'Identifies all debtors that are duplicated, under certain conditions.';

    private const ARGUMENT_OUTPUT_FILE = 'output';

    private const ARGUMENT_BROADCAST_BATCH_SIZE = 'batch';

    private const ARGUMENT_BROADCAST_SLEEP = 'sleep';

    private $duplicateFinder;

    private $duplicateHandler;

    private $slackClient;

    public function __construct(
        DebtorDuplicateFinder $duplicateFinder,
        DebtorDuplicateHandler $duplicateHandler,
        SlackClient $slackClient
    ) {
        parent::__construct();
        $this->duplicateFinder = $duplicateFinder;
        $this->duplicateHandler = $duplicateHandler;
        $this->slackClient = $slackClient;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->addOption(self::ARGUMENT_OUTPUT_FILE, null, InputOption::VALUE_REQUIRED, 'File or handle where to output the results in CSV format', 'php://stdout')
            ->addOption(self::ARGUMENT_BROADCAST_BATCH_SIZE, null, InputOption::VALUE_REQUIRED, 'Broadcast batch size', 50)
            ->addOption(self::ARGUMENT_BROADCAST_SLEEP, null, InputOption::VALUE_REQUIRED, 'Broadcast sleep time between batches, in seconds', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getOption(self::ARGUMENT_OUTPUT_FILE);

        $output->writeln('Identifying duplicates...');

        $newDuplicates = $this->process($this->duplicateFinder->find(), $filename);

        if (!empty($newDuplicates)) {
            $output->writeln('Communicating duplicates to other services...');

            $this->duplicateHandler->broadcast(
                $newDuplicates,
                (int) $input->getOption(self::ARGUMENT_BROADCAST_BATCH_SIZE),
                (int) $input->getOption(self::ARGUMENT_BROADCAST_SLEEP)
            );

            $output->writeln('Found ' . count($newDuplicates) . ' new duplicates.');
        } else {
            $output->writeln('No new duplicates found.');
        }

        $this->sendSlackMessage(count($newDuplicates));

        $output->writeln('DONE.');
    }

    private function sendSlackMessage(int $duplicateCount)
    {
        if ($duplicateCount > 0) {
            $text = "Hey, <!here>, I've found *{$duplicateCount} new* company duplicates since the last time.";
            $color = SlackMessageAttachment::COLOR_YELLOW;
        } else {
            $text = 'No company duplicates found. Cool!';
            $color = SlackMessageAttachment::COLOR_GREEN;
        }

        $message = (new SlackMessage())->addAttachment(
            (new SlackMessageAttachment($text))
                ->setTitle('Company Duplicates Finder Report')
                ->setText($text)
                ->setColor($color)
                ->addField((new SlackMessageAttachmentField('Environment', getenv('APP_ENV') ?: '-')))
                ->addField((new SlackMessageAttachmentField('Instance', getenv('INSTANCE_SUFFIX') ?: '-')))
        );

        $this->slackClient->sendMessage($message);
    }

    /**
     * @param  MerchantDebtorDuplicateDTO[]|\Generator $data
     * @param  string                                  $file
     * @return MerchantDebtorDuplicateDTO[]            The newly found duplicates
     * @throws \Exception
     */
    private function process(\Generator $data, $file): array
    {
        if (!$data->valid()) {
            return [];
        }

        if (file_exists($file)) {
            @unlink($file);
        }

        $output = fopen($file, 'w');

        $newDuplicates = [];

        $csvColumns = array_keys($this->flattenDuplicateDto($data->current()));
        $csvColumns[] = 'isNewDuplicate';
        fputcsv($output, $csvColumns);

        foreach ($data as $i => $duplicateDto) {
            $duplicateArr = $this->flattenDuplicateDto($duplicateDto);

            $isNewDuplicate = false;

            if ($this->duplicateHandler->register($duplicateDto)) {
                $newDuplicates[] = $duplicateDto;
                $isNewDuplicate = true;
            }

            $row = array_values($duplicateArr);
            $row[] = $isNewDuplicate;
            fputcsv($output, $row);
        }

        fclose($output);

        return $newDuplicates;
    }

    private function flattenDuplicateDto(MerchantDebtorDuplicateDTO $duplicateDto): array
    {
        $duplicateArr = $duplicateDto->toArray();
        $countersArr = $duplicateDto->getOrderStateCounter()->toArray();
        unset($duplicateArr['orderStateCounter']);
        foreach ($countersArr as $k => $v) {
            $duplicateArr[$k . 'Orders'] = $v;
        }

        return $duplicateArr;
    }
}
