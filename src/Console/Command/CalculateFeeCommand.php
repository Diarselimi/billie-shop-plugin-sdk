<?php

namespace App\Console\Command;

use App\DomainModel\Fee\FeeCalculator;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use Ozean12\Money\Money;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateFeeCommand extends Command
{
    private const NAME = 'paella:calculate-fee';

    private const OPTION_AMOUNT = 'amount';

    private const OPTION_DURATION = 'duration';

    private const OPTION_MERCHANT_ID = 'merchant_id';

    private MerchantSettingsRepositoryInterface $merchantSettingsRepository;

    private FeeCalculator $feeCalculator;

    public function __construct(
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        FeeCalculator $feeCalculator
    ) {
        parent::__construct(self::NAME);

        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->feeCalculator = $feeCalculator;
    }

    protected function configure()
    {
        $this
            ->addOption(self::OPTION_AMOUNT, 'a', InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_DURATION, 'd', InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_MERCHANT_ID, 'm', InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchant($input->getOption(self::OPTION_MERCHANT_ID));

        $amount = new Money($input->getOption(self::OPTION_AMOUNT));
        $fee = $this->feeCalculator->calculate($amount, $input->getOption(self::OPTION_DURATION), $merchantSettings->getFeeRates());

        $output->writeln("<info>Fee rate: </info> {$fee->getFeeRate()}");
        $output->writeln("<info>Gross fee amount: </info> {$fee->getGrossFeeAmount()}");
        $output->writeln("<info>Net fee amount: </info> {$fee->getNetFeeAmount()}");
        $output->writeln("<info>Tax fee amount: </info> {$fee->getTaxFeeAmount()}");

        return 0;
    }
}
