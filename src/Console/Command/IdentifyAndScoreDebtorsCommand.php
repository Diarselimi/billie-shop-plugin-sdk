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
            ->addOption(self::OPTION_MERCHANT_ID, null, InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_FILE, null, InputOption::VALUE_REQUIRED, 'Path to the the csv file')
            ->addOption(self::OPTION_WITH_SCORING, null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (($handle = fopen($input->getOption(self::OPTION_FILE), "r")) === false) {
            $output->writeln('<error>CSV file was not found.</error>');

            return 1;
        }

        $doScoring = ($input->getOption(self::OPTION_WITH_SCORING)) ? true : false;

        $outputReportData = [];
        $ineligibleCompanies = [];

        $headers = [];
        $i = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $data = array_map('trim', $data);

            if ($i === 0) {
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
                $useCaseRequest = (new IdentifyAndScoreDebtorRequest())
                    ->setMerchantId($input->getOption(self::OPTION_MERCHANT_ID))
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
                    ->setFirstName($debtorExternalData['first_name'])
                    ->setLastName($debtorExternalData['last_name'])
                ;

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
        }

        fclose($handle);

        if (count($ineligibleCompanies)) {
            $output->writeln(sprintf('<info>Declined companies: %s</info>', implode(', ', $ineligibleCompanies)));
        }

        $output->writeln($this->createCSVReport($outputReportData));
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
