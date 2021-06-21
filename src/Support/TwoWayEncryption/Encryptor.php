<?php

namespace App\Support\TwoWayEncryption;

interface Encryptor
{
    public const DEFAULT_ENCRYPTING_ALGORITHM = 'aes-128-ecb';

    public function encrypt(string $phrase): string;

    public function decrypt(string $encryptedPhrase): string;
}
