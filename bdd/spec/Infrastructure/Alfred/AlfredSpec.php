<?php

namespace spec\App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\SignatoryPower\SignatoryPowerDTOFactory;
use App\Infrastructure\ClientResponseDecodeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class AlfredSpec extends ObjectBehavior
{
    public function let(
        Client $alfredClient,
        DebtorCompanyFactory $debtorFactory,
        SignatoryPowerDTOFactory $signatoryPowersDTOFactory
    ) {
        $this->beConstructedWith($alfredClient, $debtorFactory, $signatoryPowersDTOFactory);
    }

    public function it_should_return_a_debtor_company_after_successful_identify_firmenwissen(
        Client $alfredClient,
        DebtorCompanyFactory $debtorFactory
    ) {
        $debtorCompany = new DebtorCompany();
        $body = '{}';
        $response = new Response(201, [], $body);
        $alfredClient->post(Argument::cetera())->willReturn($response);
        $debtorFactory->createFromAlfredResponse(Argument::any())->willReturn($debtorCompany);

        Assert::eq(
            $this->identifyFirmenwissen('123')->getWrappedObject(),
            $debtorCompany
        );
    }

    public function it_should_throw_exception_when_post_call_failed(
        Client $alfredClient
    ) {
        $alfredClient->post(Argument::cetera())->willThrow(ClientException::class);

        $this
            ->shouldThrow(CompaniesServiceRequestException::class)
            ->during('identifyFirmenwissen', ['123']);
    }

    public function it_should_throw_exception_when_decode_failed(
        Client $alfredClient,
        DebtorCompanyFactory $debtorFactory
    ) {
        $body = '{}';
        $response = new Response(201, [], $body);
        $alfredClient->post(Argument::cetera())->willReturn($response);
        $debtorFactory->createFromAlfredResponse(Argument::any())->willThrow(ClientResponseDecodeException::class);

        $this
            ->shouldThrow(CompaniesServiceRequestException::class)
            ->during('identifyFirmenwissen', ['123']);
    }
}
