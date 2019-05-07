<?php

namespace App\Console\Command;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\Application\UseCase\IdentifyAndScoreDebtor\IdentifyAndScoreDebtorRequest;
use App\Application\UseCase\IdentifyAndScoreDebtor\IdentifyAndScoreDebtorResponse;
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

    private const REPORT_FILE_HEADERS = ['external_id', 'input_name', 'identified_name', 'crefo_id', 'company_id', 'is_strict_match', 'is_eligible'];

    private const BATCH_SIZE = 5;

    private const BATCH_TIMEOUT = 2;

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
            ->addOption(self::OPTION_ALGORITHM, 'a', InputOption::VALUE_REQUIRED, '1 for experimental, 0 for normal')
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
            $output->writeln('<error>Some of the required parameters are missing.</error>');

            return 1;
        }

        if (($handle = fopen($input->getOption(self::OPTION_FILE), "r")) === false) {
            $output->writeln('<error>CSV file was not found.</error>');

            return 1;
        }

        $doScoring = $input->getOption(self::OPTION_WITH_SCORING) ? true : false;
        $merchantId = (int) $input->getOption(self::OPTION_MERCHANT_ID);
        $useExperimentalDebtorIdentification = boolval($input->getOption(self::OPTION_ALGORITHM));
        $offset = $input->getOption(self::OPTION_OFFSET);
        $limit = $input->getOption(self::OPTION_LIMIT);

        $results = [];
        $headers = [];
        $i = -1; // start with header row
        $processedCount = 0;

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
                $useCaseRequest = $this->createUseCaseRequest($debtorExternalData, $merchantId, $useExperimentalDebtorIdentification, $doScoring);
                $result = $this->useCase->execute($useCaseRequest);
                $this->addResult($results, $debtorExternalData, $result);
            } catch (MerchantNotFoundException $e) {
                $output->writeln('<error>Merchant was not found.</error>');

                return 1;
            } catch (DebtorNotIdentifiedException $e) {
                $this->addResult($results, $debtorExternalData, null);
            }

            $i++;
            $processedCount++;

            if ($processedCount == self::BATCH_SIZE) {
                sleep(self::BATCH_TIMEOUT);
                $processedCount = 0;
            }
        }

        fclose($handle);
        $output->writeln($this->createCSVReport($results));
    }

    private function addResult(array &$results, array $debtorExternalData, ?IdentifyAndScoreDebtorResponse $response)
    {
        $results[] = [
            $debtorExternalData['external_id'],
            $debtorExternalData['name'],
            $response ? $response->getCompanyName() : null,
            $response ? $response->getCrefoId() : null,
            $response ? $response->getCompanyId() : null,
            ($response && $response->isStrictMatch() === true) ? 1 : 0,
            ($response && $response->isEligible() === true) ? 1 : 0,
        ];
    }

    private function createUseCaseRequest(
        array $debtorExternalData,
        int $merchantId,
        bool $useExperimentalDebtorIdentification,
        bool $doScoring
    ): IdentifyAndScoreDebtorRequest {
        return (new IdentifyAndScoreDebtorRequest())
            ->setMerchantId($merchantId)
            ->setUseExperimentalDebtorIdentification($useExperimentalDebtorIdentification)
            ->setDoScoring($doScoring)
            ->setName($debtorExternalData['name'])
            ->setAddressHouse($debtorExternalData['house'])
            ->setAddressStreet($debtorExternalData['street'])
            ->setAddressPostalCode($debtorExternalData['postal_code'])
            ->setAddressCity($debtorExternalData['city'])
            ->setAddressCountry($debtorExternalData['country'])
            ->setTaxId($debtorExternalData['tax_id'])
            ->setTaxNumber($debtorExternalData['tax_number'] ?? null)
            ->setRegistrationNumber($debtorExternalData['registration_number'] ?? null)
            ->setRegistrationCourt($debtorExternalData['registration_court'] ?? null)
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
