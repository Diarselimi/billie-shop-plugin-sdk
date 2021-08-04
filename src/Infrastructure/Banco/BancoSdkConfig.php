<?php

declare(strict_types=1);

namespace App\Infrastructure\Banco;

use Ozean12\BancoSDK\Configuration;

class BancoSdkConfig extends Configuration
{
    public function __construct(string $bancoApiUrl)
    {
        parent::__construct();
        $this->setHost($bancoApiUrl);
    }
}
