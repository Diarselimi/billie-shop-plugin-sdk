<?php

declare(strict_types=1);

namespace App\Support\TwoWayEncryption;

class OpenSslEcbEncryption implements Encryptor
{
    private string $key;

    public function __construct(string $encryptionKey)
    {
        $this->key = $encryptionKey;
    }

    public function encrypt(string $phrase): string
    {
        if (empty($phrase)) {
            return $phrase;
        }

        $encryptedWord = \openssl_encrypt(
            $phrase,
            self::DEFAULT_ENCRYPTING_ALGORITHM,
            $this->key,
            OPENSSL_RAW_DATA
        );
        if (!is_string($encryptedWord)) {
            throw new \Exception('Encryption failed!');
        }

        return base64_encode($encryptedWord);
    }

    public function decrypt(string $encryptedPhrase): string
    {
        if (empty($encryptedPhrase)) {
            return $encryptedPhrase;
        }

        $encryptedPhrase = base64_decode($encryptedPhrase);
        $decryptedPhrase = \openssl_decrypt(
            $encryptedPhrase,
            self::DEFAULT_ENCRYPTING_ALGORITHM,
            $this->key,
            OPENSSL_RAW_DATA
        );

        if (!is_string($decryptedPhrase)) {
            throw new \Exception('Decryption failed!');
        }

        return $decryptedPhrase;
    }
}
