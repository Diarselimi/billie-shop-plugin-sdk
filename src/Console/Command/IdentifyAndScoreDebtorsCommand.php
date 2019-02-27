<?php

namespace App\Console\Command;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\Application\UseCase\IdentifyAndScoreDebtor\IdentifyAndScoreDebtorRequest;
use App\Application\UseCase\IdentifyAndScoreDebtor\IdentifyAndScoreDebtorUseCase;
use App\DomainModel\Merchant\MerchantNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IdentifyAndScoreDebtorsCommand extends Command
{
    private const NAME = 'paella:identify-and-score-debtors';

    private const DESCRIPTION = 'Identify and score the given list of debtors';

    private const OPTION_MERCHANT_ID = 'merchant-id';

    private const OPTION_FILE = 'file';

    private const OPTION_WITH_SCORING = 'with-scoring';

    private const OPTION_ALGORITHM = 'algorithm';

    private const OPTION_OFFSET = 'offset';

    private const OPTION_LIMIT = 'limit';

    private const REPORT_FILE_HEADERS = ['external_id', 'company_id', 'is_eligible'];

    private $useCase;

    public function __construct(IdentifyAndScoreDebtorUseCase $useCase)
    {
        parent::__construct();

        $this->useCase = $useCase;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->addOption(self::OPTION_MERCHANT_ID, 'm', InputOption::VALUE_REQUIRED, 'Merchant ID')
            ->addOption(self::OPTION_FILE, 'f', InputOption::VALUE_REQUIRED, 'Path to the the csv file')
            ->addOption(self::OPTION_WITH_SCORING, 's', InputOption::VALUE_REQUIRED, 'Run the scoring: 1 or 0', false)
            ->addOption(self::OPTION_ALGORITHM, 'a', InputOption::VALUE_REQUIRED, 'Identification algorithm: v1 or v2')
            ->addOption(self::OPTION_OFFSET, 'o', InputOption::VALUE_REQUIRED, 'Offset in the cvs', 0)
            ->addOption(self::OPTION_LIMIT, 'l', InputOption::VALUE_REQUIRED, 'Limit in the csv', -1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_MERCHANT_ID) === null
            || $input->getOption(self::OPTION_FILE) === null
            || $input->getOption(self::OPTION_ALGORITHM) === null
        ) {
            $output->writeln('<error>Some of the required paramerters are missing.</error>');

            return 1;
        }

        if (($handle = fopen($input->getOption(self::OPTION_FILE), "r")) === false) {
            $output->writeln('<error>CSV file was not found.</error>');

            return 1;
        }

        $doScoring = $input->getOption(self::OPTION_WITH_SCORING) ? true : false;
        $merchantId = (int) $input->getOption(self::OPTION_MERCHANT_ID);
        $algorithm = $input->getOption(self::OPTION_ALGORITHM);
        $offset = $input->getOption(self::OPTION_OFFSET);
        $limit = $input->getOption(self::OPTION_LIMIT);

        $outputReportData = [];
        $ineligibleCompanies = [];

        $headers = [];
        $i = -1; // start with header row

        while (($data = fgetcsv($handle)) !== false) {
            $data = array_map('trim', $data);

            if ($i === -1) {
                $headers = array_values($data);
                $i++;

                continue;
            }

            if ($i < $offset || ($limit !== -1 && $i >= $limit)) {
                $i++;

                continue;
            }

            $debtorExternalData = array_combine($headers, $data);

            $output->writeln(
                sprintf('<info>Identifying debtor: %s</info>', $debtorExternalData['external_id']),
                OutputInterface::VERBOSITY_VERBOSE
            );

            try {
                $useCaseRequest = $this->createUseCaseRequest($debtorExternalData, $merchantId, $algorithm, $doScoring);
                $result = $this->useCase->execute($useCaseRequest, $doScoring);

                if ($result->isEligible() === false) {
                    $ineligibleCompanies[] = $result->getCompanyId();
                }

                $outputReportData[] = [
                    $debtorExternalData['external_id'],
                    $result->getCompanyId(),
                    ($result->isEligible() === true) ? 1 : 0,
                ];
            } catch (MerchantNotFoundException $e) {
                $output->writeln('<error>Merchant was not found.</error>');

                return 1;
            } catch (DebtorNotIdentifiedException $e) {
                $outputReportData[] = [$debtorExternalData['external_id'], null, null];
            }

            $i++;
        }

        fclose($handle);

        if (count($ineligibleCompanies)) {
            $output->writeln(sprintf('<info>Declined companies: %s</info>', implode(', ', $ineligibleCompanies)));
        }

        $output->writeln($this->createCSVReport($outputReportData));
    }

    private function createUseCaseRequest(array $debtorExternalData, int $merchantId, string $algorithm, bool $doScoring): IdentifyAndScoreDebtorRequest
    {
        return (new IdentifyAndScoreDebtorRequest())
            ->setMerchantId($merchantId)
            ->setAlgorithm($algorithm)
            ->setDoScoring($doScoring)
            ->setName($debtorExternalData['name'])
            ->setAddressHouse($debtorExternalData['house'])
            ->setAddressStreet($debtorExternalData['street'])
            ->setAddressPostalCode($debtorExternalData['postal_code'])
            ->setAddressCity($debtorExternalData['city'])
            ->setAddressCountry($debtorExternalData['country'])
            ->setTaxId($debtorExternalData['tax_id'])
            ->setTaxNumber($debtorExternalData['tax_number'])
            ->setRegistrationNumber($debtorExternalData['registration_number'])
            ->setRegistrationCourt($debtorExternalData['registration_court'])
            ->setLegalForm($debtorExternalData['legal_form'])
            ->setFirstName($debtorExternalData['first_name'] ?? null)
            ->setLastName($debtorExternalData['last_name'] ?? null)
        ;
    }

    private function createCSVReport(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, self::REPORT_FILE_HEADERS);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);

        $csv = stream_get_contents($output);

        fclose($output);

        return $csv;
    }
}
