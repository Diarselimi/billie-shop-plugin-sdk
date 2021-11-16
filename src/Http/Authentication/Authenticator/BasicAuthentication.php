<?php

declare(strict_types=1);

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\Http\Authentication\MerchantApiUser;
use App\UserInterface\Authentication\Credentials\CredentialProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BasicAuthentication extends AbstractAuthenticator
{
    private CredentialProvider $credentialProvider;

    public function __construct(CredentialProvider $credentialProvider, MerchantRepository $merchantRepository)
    {
        parent::__construct($merchantRepository);
        $this->credentialProvider = $credentialProvider;
        $this->merchantRepository = $merchantRepository;
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization') && stripos(
            $request->headers->get('Authorization'),
            'Basic'
        ) === 0;
    }

    public function getCredentials(Request $request): array
    {
        [$user, $pass] = $this->sanitizeAndDecodeToken($request);

        return [
            'user' => $user,
            'password' => $pass,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): MerchantApiUser
    {
        if ($this->credentialProvider->getUser() === $credentials['user'] && $this->credentialProvider->getPassword() === $credentials['password']) {
            return new MerchantApiUser(new MerchantEntity());
        }

        throw new AuthenticationException();
    }

    /**
     * @param  Request        $request
     * @return false|string[]
     */
    private function sanitizeAndDecodeToken(Request $request)
    {
        $basicToken = substr($request->headers->get('Authorization'), strlen('Basic '));

        $decodedToken = urldecode(base64_decode($basicToken));

        return explode(':', $decodedToken);
    }
}
