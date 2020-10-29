<?php

declare(strict_types=1);

namespace App\Infrastructure\VatRate;

use App\DomainModel\VatRate\VatRateRepositoryInterface;
use Ozean12\Money\Percent;
use Symfony\Component\Yaml\Yaml;

class VatRateRepository implements VatRateRepositoryInterface
{
    /**
     * @var string
     */
    private $vatYamlPath;

    public function __construct(string $vatRatesYamlPath)
    {
        $this->vatYamlPath = $vatRatesYamlPath;
    }

    public function getForDateTime(\DateTime $currentDateTime): Percent
    {
        static $rates = null;

        if ($rates === null) {
            $file = Yaml::parseFile($this->vatYamlPath);
            $rates = $file['vat_rates'];
        }

        foreach ($rates as $rate) {
            $dateTime = new \DateTime($rate['valid_from']);
            if ($dateTime <= $currentDateTime) {
                return new Percent($rate['vat_rate']);
            }
        }

        throw new \LogicException('Vat Rate cannot be found for the given date.');
    }
}
