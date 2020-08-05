<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Sandbox\SandboxMerchantCreationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSandboxMerchantsCommand extends Command
{
    private const NAME = 'paella:create-sandbox-merchants';

    private $merchantRepository;

    private $creationService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        SandboxMerchantCreationService $creationService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->creationService = $creationService;

        parent::__construct(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $merchants MerchantEntity[]
         */
        $merchants = $this->merchantRepository->getMerchantsWithoutSandbox();

        if (empty($merchant)) {
            $output->writeln('<info>All merchants have a sandbox </info>', OutputInterface::VERBOSITY_VERBOSE);
        }

        foreach ($merchants as $merchant) {
            try {
                $this->creationService->create($merchant);

                $output->writeln(
                    sprintf('<info>Sandbox successfully created for merchant: %s</info>', $merchant->getId()),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            } catch (\Exception $exception) {
                $output->writeln(
                    sprintf('<error>Sandbox merchant creation failed for merchant %s.</error>', $merchant->getId()),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }

        return 0;
    }
}
