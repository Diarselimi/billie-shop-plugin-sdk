<?php

declare(strict_types=1);

namespace App\Support\TwoWayEncryption;

class EncryptorImpl implements Encryptor
{
    private const STRING_SEPARATOR = '::';

    private string $randomBytes;

    private string $randomBytesHex;

    private string $key;

    public function __construct(string $encryptionKey)
    {
        $this->key = $encryptionKey;
        $this->generateRandomBytes();
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
            0,
            $this->randomBytes
        );
        if (!is_string($encryptedWord)) {
            throw new \Exception('Encryption failed!');
        }

        return $encryptedWord . self::STRING_SEPARATOR . $this->randomBytesHex;
    }

    public function decrypt(string $encryptedPhrase): string
    {
        $this->validateDecodeString($encryptedPhrase);

        list($actualEncryptedPhrase, $randomBytes) = explode(self::STRING_SEPARATOR, $encryptedPhrase);
        $decryptedPhrase = \openssl_decrypt(
            $actualEncryptedPhrase,
            self::DEFAULT_ENCRYPTING_ALGORITHM,
            $this->key,
            0,
            hex2bin($randomBytes)
        );

        if (!is_string($decryptedPhrase)) {
            throw new \Exception('Decryption failed!');
        }

        return $decryptedPhrase;
    }

    public function getGeneratedBytes(): string
    {
        return $this->randomBytes;
    }

    private function validateDecodeString(string $encryptedPhrase): void
    {
        if (strpos($encryptedPhrase, self::STRING_SEPARATOR) === false) {
            throw new \Exception('Not recognised encryption format.');
        }
    }

    private function generateRandomBytes(): void
    {
        $generatedBytes = \openssl_random_pseudo_bytes(
            \openssl_cipher_iv_length(self::DEFAULT_ENCRYPTING_ALGORITHM)
        );

        if (!is_string($generatedBytes)) {
            throw new \Exception('OpenSsl could not be initialised!');
        }
        $this->randomBytes = $generatedBytes;
        $this->randomBytesHex = bin2hex($generatedBytes);
        unset($generatedBytes);
    }
}
