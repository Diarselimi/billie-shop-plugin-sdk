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

    const OPTION_SINCE = 'since';

    private const OUTPUT_REPORT_FILE_HEADERS = [
        'external_id',
        'company_name',
        'address',
        'extracted_legal_form',
        'identified_company_id',
        'identified_name',
        'identified_address',
        'found',
        'matches',
        'unlinked',
        'score',
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
            ->addOption(self::OPTION_SINCE, 's', InputOption::VALUE_OPTIONAL, 'Since what line to run?', 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stdout = fopen('php://stdout', 'r+');
        $stderr = fopen('php://stderr', 'r+');

        $isDryRun = $input->getOption(self::OPTION_DRYRUN) !== 'false';
        fwrite(
            $stderr,
            sprintf(
                'Running the command as a dry_run = "%s" in 5 seconds...' . "\n\n",
                $isDryRun ? 'true' : 'false'
            )
        );
        sleep(5);

        if (($handle = fopen($input->getOption(self::OPTION_FILE), "r")) === false) {
            $output->writeln('<error>CSV file was not found.</error>');

            return 1;
        }

        $merchantId = (int) $input->getOption(self::OPTION_MERCHANT_ID);
        $headers = [];

        $i = -1; // start with header row
        $j = $input->getOption(self::OPTION_SINCE); // since what iterator to begin with
        $totalLines = $this->calculateFileLines($input->getOption(self::OPTION_FILE));

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $rawData = $data;
            $data = array_map('trim', $data);

            fwrite(
                $stderr,
                sprintf(
                    "\rProcessing the line %d [%.10f%%] [Debtor external id \"%s\"]",
                    $i,
                    max($i, 0) / $totalLines * 100,
                    $data[0]
                )
            );

            if ($i === -1) {
                $headers = array_values($data);
                $this->csvCreateHeader($stdout);
                $i++;

                continue;
            }

            if ($i + 2 < $j) { // start from 10th __LINE__ in the original csv file.
                $i++;

                continue;
            }

            $debtorExternalData = array_combine($headers, $data);

            if ($debtorExternalData === false) {
                $output->writeln(
                    sprintf('<info>Failed to parse/combine row "%s"</info>', $rawData),
                    OutputInterface::VERBOSITY_VERBOSE
                );

                continue;
            }

            $output->writeln(
                sprintf('<info>Identifying debtor: %s</info>', $debtorExternalData['external_id']),
                OutputInterface::VERBOSITY_VERBOSE
            );

            $result = $this->service->process(
                $debtorExternalData,
                $merchantId,
                $isDryRun
            );

            $this->csvWriteLine($stdout, $result);

            $i++;
        }

        fclose($stdout);
        fclose($stderr);
        fclose($handle);

        return 0;
    }

    private function csvCreateHeader($resource)
    {
        fputcsv($resource, self::OUTPUT_REPORT_FILE_HEADERS);
    }

    private function csvWriteLine($resource, array $row)
    {
        fputcsv($resource, array_intersect_key($row, array_flip(self::OUTPUT_REPORT_FILE_HEADERS)));
    }

    private function calculateFileLines(string $filePath): int
    {
        $lines = 0;

        $handle = fopen($filePath, "r");
        while (!feof($handle)) {
            fgets($handle);
            $lines++;
        }
        fclose($handle);

        return $lines;
    }
}
