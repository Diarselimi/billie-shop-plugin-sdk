<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Support\StreetHouseParser;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Request;

class AddressRequestFactory implements LoggingInterface
{
    use LoggingTrait;

    public function create(Request $request, string $fieldName): ?CreateOrderAddressRequest
    {
        $data = $request->request->get($fieldName);

        if (!is_array($data) || empty($data)) {
            return null;
        }

        return $this->createFromArray($data);
    }

    public function createFromArray(?array $data): ?CreateOrderAddressRequest
    {
        if ($data === null) {
            return null;
        }

        [$street, $house] = $this->extractStreetAndHouse(
            $data['street'] ?? null,
            $data['house_number'] ?? null
        );

        return (new CreateOrderAddressRequest())
            ->setAddition($data['addition'] ?? null)
            ->setHouseNumber($house)
            ->setStreet($street)
            ->setPostalCode($data['postal_code'] ?? null)
            ->setCity($data['city'] ?? null)
            ->setCountry(
                $this->normalizeCountry($data['country'] ?? null)
            );
    }

    /**
     * @deprecated don't use this for new endpoints, only existing ones
     */
    public function createFromOldFormat(array $requestData): CreateOrderAddressRequest
    {
        [$street, $house] = $this->extractStreetAndHouse(
            $requestData['address_street'] ?? null,
            $requestData['address_house_number'] ?? null
        );

        return (new CreateOrderAddressRequest())
            ->setAddition($requestData['address_addition'] ?? null)
            ->setHouseNumber($house)
            ->setStreet($street)
            ->setCity($requestData['address_city'] ?? null)
            ->setPostalCode($requestData['address_postal_code'] ?? null)
            ->setCountry(
                $this->normalizeCountry($requestData['address_country'] ?? null)
            );
    }

    private function extractStreetAndHouse(?string $inputStreet, ?string $inputHouse): array
    {
        [$outputStreet, $outputHouse] = [$inputStreet, $inputHouse];

        if (empty($inputHouse) && !empty($inputStreet)) {
            [$outputStreet, $outputHouse] = StreetHouseParser::extractStreetAndHouse($inputStreet);
            $this->logInfo('Extracted street and house from input', [
                LoggingInterface::KEY_SOBAKA => [
                    'input' => $inputStreet,
                    'street' => $outputStreet,
                    'house' => $outputHouse,
                ],
            ]);
        }

        return [$outputStreet, $outputHouse];
    }

    private function normalizeCountry(?string $country): ?string
    {
        return $country === null ? null : strtoupper($country);
    }
}
