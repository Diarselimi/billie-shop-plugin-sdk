<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\Authentication\SignatoryPowerTokenUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SignatoryPowerTokenAuthenticator extends AbstractAuthenticator
{
    private $companiesService;

    public function __construct(CompaniesServiceInterface $companiesService, MerchantRepositoryInterface $merchantRepository)
    {
        $this->companiesService = $companiesService;
        parent::__construct($merchantRepository);
    }

    public function supports(Request $request)
    {
        return $request->attributes->has('token');
    }

    public function getCredentials(Request $request)
    {
        return $request->attributes->get('token');
    }

    public function getUser($token, UserProviderInterface $userProvider)
    {
        $signatoryPower = $this->companiesService->getSignatoryPowerDetails($token);

        if (!$signatoryPower || !$signatoryPower->getCompanyUuid()) {
            throw new AuthenticationException('Invalid token');
        }

        $merchant = $this->merchantRepository->getOneByCompanyUuid($signatoryPower->getCompanyUuid());

        if (!$merchant) {
            throw new AuthenticationException('Invalid token');
        }

        return new SignatoryPowerTokenUser($merchant, $signatoryPower);
    }
}
