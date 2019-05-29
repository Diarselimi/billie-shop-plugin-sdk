<?php

namespace spec\App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorResponse;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorResponseFactory;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetMerchantDebtorUseCaseSpec extends ObjectBehavior
{
    private const DEBTOR_ID = '5000';

    private const MERCHANT_ID = 100;

    private const MERCHANT_DEBTOR_ID = 15;

    private const MERCHANT_DEBTOR_PAYMENT_ID = 'uuid123';

    private const MERCHANT_DEBTOR_EXTERNAL_ID = 'TE56DD';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorResponseFactory $merchantDebtorResponseFactory
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $merchantDebtorResponseFactory);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GetMerchantDebtorUseCase::class);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request
    ) {
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);

        $merchantDebtorRepository->getOneByMerchantExternalId(self::MERCHANT_DEBTOR_EXTERNAL_ID, self::MERCHANT_ID, [])->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_returns_the_merchant_debtor(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request,
        MerchantDebtorEntity $merchantDebtor,
        DebtorPaymentDetailsDTO $debtorPaymentDetails,
        DebtorCompany $debtorDTO,
        GetMerchantDebtorResponseFactory $merchantDebtorResponseFactory
    ) {
        $this->mockDebtorPaymentDetails($debtorPaymentDetails);
        $this->mockDebtor($debtorDTO);

        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);

        $merchantDebtorRepository->getOneByMerchantExternalId(self::MERCHANT_DEBTOR_EXTERNAL_ID, self::MERCHANT_ID, [])->shouldBeCalledOnce()->willReturn($merchantDebtor);

        $this->mockMerchantDebtorResponseFactory($merchantDebtorResponseFactory, $merchantDebtor, $request);

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(GetMerchantDebtorResponse::class);
    }

    private function mockDebtorPaymentDetails(DebtorPaymentDetailsDTO $debtorPaymentDetails): void
    {
        $debtorPaymentDetails->getOutstandingAmount()->willReturn(600.);
    }

    private function mockDebtor(DebtorCompany $debtor): void
    {
        $debtor->getId()->willReturn(self::DEBTOR_ID);
        $debtor->getName()->willReturn('Dummy name');
        $debtor->getAddressHouse()->willReturn('55');
        $debtor->getAddressCity()->willReturn('City');
        $debtor->getAddressStreet()->willReturn('Street');
        $debtor->getAddressPostalCode()->willReturn('00567');
        $debtor->getAddressCountry()->willReturn('DE');
        $debtor->getCrefoId()->willReturn('crefo_id');
        $debtor->getSchufaId()->willReturn('schufa_id');
    }

    private function mockMerchantDebtorResponseFactory(GetMerchantDebtorResponseFactory $factory, MerchantDebtorEntity $debtorEntity, GetMerchantDebtorRequest $request)
    {
        $merchantDebtorData['id'] = self::MERCHANT_DEBTOR_ID;
        $merchantDebtorData['company_id'] = self::DEBTOR_ID;
        $merchantDebtorData['payment_id'] = self::MERCHANT_DEBTOR_PAYMENT_ID;
        $merchantDebtorData['external_id'] = self::MERCHANT_DEBTOR_EXTERNAL_ID;
        $merchantDebtorData['available_limit'] = 5000.;
        $merchantDebtorData['created_amount'] = 150.55;
        $merchantDebtorData['outstanding_amount'] = 600.;
        $merchantDebtorData['total_limit'] = 5750.55;
        $merchantDebtorData['company'] = [
            'crefo_id' => 'crefo_id',
            'schufa_id' => 'schufa_id',
            'name' => 'Dummy name',
            'address_house' => '55',
            'address_street' => 'Street',
            'address_city' => 'City',
            'address_postal_code' => '00567',
            'address_country' => 'DE',
        ];
        $merchantDebtor = new GetMerchantDebtorResponse($merchantDebtorData);
        $factory->create(Argument::any(), Argument::any())->willReturn($merchantDebtor);
    }
}
