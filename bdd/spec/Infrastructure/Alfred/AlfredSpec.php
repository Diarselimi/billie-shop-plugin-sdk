<?php

namespace spec\App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\IdentifyDebtorResponseDTOFactory;
use App\DomainModel\ExternalDebtorResponse\ExternalDebtorFactory;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTOFactory;
use App\DomainModel\SignatoryPower\SignatoryPowerDTOFactory;
use App\Infrastructure\ClientResponseDecodeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class AlfredSpec extends ObjectBehavior
{
    public function let(
        Client $alfredClient,
        DebtorCompanyFactory $debtorFactory,
        SignatoryPowerDTOFactory $signatoryPowersDTOFactory,
        ExternalDebtorFactory $externalDebtorFactory,
        IdentityVerificationCaseDTOFactory $identityVerificationCaseDTOFactory,
        IdentifyDebtorResponseDTOFactory $similarCandidateDTOFactory
    ) {
        $this->beConstructedWith(...func_get_args());
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

    public function it_should_get_identity_verification_case(
        Client $alfredClient,
        IdentityVerificationCaseDTOFactory $identityVerificationCaseDTOFactory
    ): void {
        $caseUuid = Uuid::uuid4()->toString();
        $data = [
            'id' => 1,
            'user_id' => 1,
            'external_code' => '0AAAAAA0AAAA',
            'url' => 'https://postident.deutschepost.de/identportal/?vorgangsnummer=0AAAAAA0AAAA',
            'valid_till' => '2020-01-01 00:00:00',
            "response" => '{\"caseId\":\"0AAAAAA0AAAA\",\"otherField\":\"otherData\"}',
            'case_status' => 'closed',
            'identification_status' => 'declined',
            'identification_method' => 'video',
            'uuid' => $caseUuid,
            'is_current' => 1,
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
        ];
        $response = new Response(200, [], json_encode($data));
        $alfredClient->get("identity-verification/{$caseUuid}")->willReturn($response);
        $identityVerificationCaseDTO = new IdentityVerificationCaseDTO();
        $identityVerificationCaseDTOFactory->createFromArray($data)->willReturn($identityVerificationCaseDTO);

        $this->getIdentityVerificationCase($caseUuid)->shouldBe($identityVerificationCaseDTO);
    }

    public function it_should_throw_exception_when_get_identity_verification_case_failed(
        Client $alfredClient
    ): void {
        $alfredClient->get(Argument::any())->willThrow(TransferException::class);

        $this
            ->shouldThrow(CompaniesServiceRequestException::class)
            ->during('getIdentityVerificationCase', [Uuid::uuid4()->toString()]);
    }
}
