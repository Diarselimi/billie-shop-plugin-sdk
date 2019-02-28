<?php

namespace App\Console\Command;

use App\DomainModel\MerchantDebtor\DebtorDuplicateFinder;
use App\DomainModel\MerchantDebtor\DebtorDuplicateHandler;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IdentifyDebtorDuplicatesCommand extends Command
{
    private const NAME = 'paella:identify-debtor-duplicates';

    private const DESCRIPTION = 'Identifies all debtors that are duplicated, under certain conditions.';

    private const ARGUMENT_OUTPUT_FILE = 'output';

    private $duplicateFinder;

    private $duplicateHandler;

    public function __construct(
        DebtorDuplicateFinder $duplicateFinder,
        DebtorDuplicateHandler $duplicateHandler
    ) {
        parent::__construct();
        $this->duplicateFinder = $duplicateFinder;
        $this->duplicateHandler = $duplicateHandler;
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->addArgument(
                self::ARGUMENT_OUTPUT_FILE,
                InputArgument::OPTIONAL,
                'File or handle where to output the results in CSV format.',
                'php://stdout'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument(self::ARGUMENT_OUTPUT_FILE);

        $output->writeln('Identifying duplicates...');

        $newDuplicates = $this->process($this->duplicateFinder->find(), $filename);

        if (!empty($newDuplicates)) {
            $output->writeln('Communicating duplicates to other services...');
            $this->duplicateHandler->broadcast($newDuplicates, 100);
            $output->writeln('Found ' . count($newDuplicates) . ' new duplicates.');
        } else {
            $output->writeln('No new duplicates found.');
        }

        $output->writeln('DONE.');
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
