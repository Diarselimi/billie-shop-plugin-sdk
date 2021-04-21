<?php

namespace App\Console\Command;

use App\DomainModel\IdentifyAndRemoveWrongIdentifications\IdentifyAndRemoveWrongIdentificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IdentifyAndRemoveWrongIdentificationsCommand extends Command
{
    private const OPTION_MERCHANT_ID = 'merchant-id';

    private const OPTION_FILE = 'file';

    private const OPTION_DRYRUN = 'dryrun';

    private const OUTPUT_REPORT_FILE_HEADERS = [
        'external_id',
        'company_name',
        'identified_company_id',
        'identified_name',
        'found',
        'matches',
        'unlinked',
    ];

    protected static $defaultName = 'paella:debtors:remove-wrong-identifications';

    private IdentifyAndRemoveWrongIdentificationService $service;

    public function __construct(IdentifyAndRemoveWrongIdentificationService $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Identify the possible wrong identification and remove the known customer check')
            ->addOption(self::OPTION_FILE, 'f', InputOption::VALUE_REQUIRED, 'Merchant ID')
            ->addOption(self::OPTION_MERCHANT_ID, 'm', InputOption::VALUE_REQUIRED, 'Path to the the csv file')
            ->addOption(self::OPTION_DRYRUN, 'd', InputOption::VALUE_OPTIONAL, 'Should be a dry run?', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun = $input->getOption(self::OPTION_DRYRUN) !== 'false';
        $output->writeln(
            sprintf(
                '<info>Running the command as a dry_run = "%s" in 5 seconds...</info>',
                $isDryRun ? 'true' : 'false'
            )
        );
        sleep(5);

        if (($handle = fopen($input->getOption(self::OPTION_FILE), "r")) === false) {
            $output->writeln('<error>CSV file was not found.</error>');

            return 1;
        }

        $merchantId = (int) $input->getOption(self::OPTION_MERCHANT_ID);
        $results = [];
        $headers = [];
        $i = -1; // start with header row

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_map('trim', $data);

            if ($i === -1) {
                $headers = array_values($data);
                $i++;

                continue;
            }

            $debtorExternalData = array_combine($headers, $data);

            $output->writeln(
                sprintf('<info>Identifying debtor: %s</info>', $debtorExternalData['external_id']),
                OutputInterface::VERBOSITY_VERBOSE
            );

            try {
                $result = $this->service->process(
                    $debtorExternalData,
                    $merchantId,
                    $isDryRun
                );
                $results[] = $result;
            } catch (\Exception  $exception) {
//                $output->writeln('<error>Merchant was not found.</error>');
                throw $exception;
            }

            $i++;
        }

        fclose($handle);
        $output->writeln($this->createCSVReport($results));

        return 0;
    }

    private function createCSVReport(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, self::OUTPUT_REPORT_FILE_HEADERS);

        foreach ($data as $row) {
            fputcsv($output, array_intersect_key($row, array_flip(self::OUTPUT_REPORT_FILE_HEADERS)));
        }

        rewind($output);

        $csv = stream_get_contents($output);

        fclose($output);

        return $csv;
    }
}
