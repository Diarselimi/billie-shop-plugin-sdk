<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\Authentication\MerchantApiUser;
use App\Http\HttpConstantsInterface;
use App\Support\TwoWayEncryption\Encryptor;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator implements LoggingInterface
{
    use LoggingTrait;

    private Encryptor $encrypt;

    public function __construct(Encryptor $encrypt, MerchantRepositoryInterface $merchantRepository)
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

        if ($merchant !== null) {
            return new MerchantApiUser($this->assertValidMerchant($merchant));
        }

        //backward compatibility if encryption release doesn't work.
        //will be removed after some trial time.
        $merchantApi = $this->merchantRepository->getOneByApiKey($apiKey);
        if ($merchantApi !== null) {
            $this->logInfo('Encryption debug info', [
                LoggingInterface::KEY_SOBAKA => [
                    'plain_key' => "<$apiKey>",
                    'encrypted_key' => "<$encryptedPhrase>",
                    'decrypted_key' => "<{$this->encrypt->decrypt($encryptedPhrase)}>",
                ],
            ]);
            $this->logSuppressedException(
                new \Exception('Auth failed'),
                sprintf('Encryption failed for merchant %s', $merchantApi->getId())
            );
        }

        return new MerchantApiUser($this->assertValidMerchant($merchantApi));
    }
}
