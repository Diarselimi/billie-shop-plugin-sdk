<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCreationDTO;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupSandboxDebtorsCommand extends Command
{
    private const NAME = 'paella:debtor:create-sandbox-debtors';

    private $debtorLimitService;

    private $companiesService;

    public function __construct(
        DebtorLimitServiceInterface $debtorLimitService,
        CompaniesServiceInterface $companiesService
    ) {
        $this->debtorLimitService = $debtorLimitService;
        $this->companiesService = $companiesService;

        parent::__construct(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createRiskPolicyDeclineReasonDebtor();
        $this->createDebtorAddressDeclineReasonDebtor();
        $this->createDebtorLimitExceededDeclineReasonDebtor();
    }

    protected function createRiskPolicyDeclineReasonDebtor(): void
    {
        $debtor = (new DebtorCreationDTO())
            ->setName('Risk Policy')
            ->setAddressCity('Berlin')
            ->setAddressCountry('DE')
            ->setAddressHouse('4')
            ->setAddressPostalCode('10969')
            ->setAddressStreet('Charlottenstraße')
            ->setCrefoId('crefoId')
            ->setLegalForm('GmbH')
            ->setRegistrationNumber('Regnummer')
            ->setSchufaId('SchufaID')
            ->setTaxId('TaxID');

        $debtorCompany = $this->companiesService->createDebtor($debtor);
        $this->debtorLimitService->create($debtorCompany->getUuid(), null, 25000);
        $this->companiesService->blacklistCompany($debtorCompany->getUuid());
    }

    protected function createDebtorAddressDeclineReasonDebtor(): void
    {
        $debtor = (new DebtorCreationDTO())
            ->setName('Debtor Address')
            ->setAddressCity('Berlin')
            ->setAddressCountry('DE')
            ->setAddressHouse('')
            ->setAddressPostalCode('10969')
            ->setAddressStreet('Charlottenstraße')
            ->setCrefoId('crefoId')
            ->setLegalForm('GmbH')
            ->setRegistrationNumber('Regnummer')
            ->setSchufaId('SchufaID')
            ->setTaxId('TaxID');

        $debtorCompany = $this->companiesService->createDebtor($debtor);
        $this->debtorLimitService
            ->create($debtorCompany->getUuid(), null, 25000);
    }

    protected function createDebtorLimitExceededDeclineReasonDebtor(): void
    {
        $debtor = (new DebtorCreationDTO())
            ->setName('Debtor Limit Exceeded')
            ->setAddressCity('Berlin')
            ->setAddressCountry('DE')
            ->setAddressHouse('4')
            ->setAddressPostalCode('10969')
            ->setAddressStreet('Charlottenstraße')
            ->setCrefoId('crefoId')
            ->setLegalForm('GmbH')
            ->setRegistrationNumber('Regnummer')
            ->setSchufaId('SchufaID')
            ->setTaxId('TaxID');

        $debtorCompany = $this->companiesService->createDebtor($debtor);
        $this->debtorLimitService
            ->create($debtorCompany->getUuid(), null, 0);
    }
}
