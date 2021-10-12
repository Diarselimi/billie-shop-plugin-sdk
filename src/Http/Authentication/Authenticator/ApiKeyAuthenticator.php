<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepository;
use App\Http\Authentication\MerchantApiUser;
use App\Http\HttpConstantsInterface;
use App\Support\TwoWayEncryption\Encryptor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private Encryptor $encrypt;

    public function __construct(Encryptor $encrypt, MerchantRepository $merchantRepository)
    {
        parent::__construct($merchantRepository);

        $this->encrypt = $encrypt;
    }

    public function supports(Request $request): bool
    {
        if ($this->wasAlreadyAuthenticated($request)) {
            return false;
        }

        return $request->headers->has(HttpConstantsInterface::REQUEST_HEADER_API_KEY);
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_KEY);
    }

    public function getUser($apiKey, UserProviderInterface $userProvider)
    {
        $encryptedPhrase = $this->encrypt->encrypt($apiKey);
        $merchant = $this->merchantRepository->getOneByApiKey($encryptedPhrase);

        return new MerchantApiUser($this->assertValidMerchant($merchant));
    }
}
