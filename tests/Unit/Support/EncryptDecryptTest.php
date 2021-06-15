<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\TwoWayEncryption\EncryptorImpl;
use App\Tests\Unit\UnitTestCase;

class EncryptDecryptTest extends UnitTestCase
{
    /** @test */
    public function itShouldDecryptThePhraseToThePlainText()
    {
        $encryptDecrypt = new EncryptorImpl('key');

        $encryptionMethod = \openssl_get_cipher_methods()[0];
        $generatedBytes = \openssl_random_pseudo_bytes(
            \openssl_cipher_iv_length($encryptionMethod)
        );
        $encryptedWord = \openssl_encrypt('some_word', $encryptionMethod, 'key', 0, $generatedBytes);
        $encryptedWord .= '::'. bin2hex($generatedBytes);
        self::assertEquals('some_word', $encryptDecrypt->decrypt($encryptedWord));
    }

    /** @test */
    public function itShouldEncryptThePlainTextToMatchTheKey()
    {
        $encryptDecrypt = new EncryptorImpl('key');

        $encryptionMethod = \openssl_get_cipher_methods()[0];
        $encryptedWord = \openssl_encrypt(
            'some_word',
            $encryptionMethod,
            'key',
            0,
            $encryptDecrypt->getGeneratedBytes()
        );
        $encryptedWord .= '::'.bin2hex($encryptDecrypt->getGeneratedBytes());

        self::assertEquals($encryptedWord, $encryptDecrypt->encrypt('some_word'));
    }

    /** @test */
    public function itShouldNotEncryptEmptyStringOrNull()
    {
        $encryptDecrypt = new EncryptorImpl('key');

        $encryptionMethod = \openssl_get_cipher_methods()[0];
        $encryptedWord = \openssl_encrypt(
            'some_word',
            $encryptionMethod,
            'key',
            0,
            $encryptDecrypt->getGeneratedBytes()
        );

        self::assertEquals('', $encryptDecrypt->encrypt(''));
    }
}
