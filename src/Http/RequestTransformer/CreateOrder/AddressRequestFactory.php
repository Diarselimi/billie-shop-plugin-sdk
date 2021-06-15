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

    private StreetHouseParser $streetHouseParser;

    public function __construct(StreetHouseParser $streetHouseParser)
    {
        $this->streetHouseParser = $streetHouseParser;
    }

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

        $street = $data['street'] ?? null;
        $house = $data['house_number'] ?? null;
        if (empty($data['house_number']) && !empty($data['street'])) {
            [$street, $house] = $this->streetHouseParser->extractStreetAndHouse($data['street']);
            $this->logInfo('Extracted street and house from input', [
                LoggingInterface::KEY_SOBAKA => [
                    'input' => $data['street'],
                    'street' => $street,
                    'house' => $house,
                ],
            ]);
        }

        return (new CreateOrderAddressRequest())
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
        return (new CreateOrderAddressRequest())
            ->setAddition($requestData['address_addition'] ?? null)
            ->setHouseNumber($requestData['address_house_number'] ?? null)
            ->setStreet($requestData['address_street'] ?? null)
            ->setCity($requestData['address_city'] ?? null)
            ->setPostalCode($requestData['address_postal_code'] ?? null)
            ->setCountry(
                $this->normalizeCountry($requestData['address_country'] ?? null)
            );
    }

    private function normalizeCountry(?string $country): ?string
    {
        return $country === null ? null : strtoupper($country);
    }
}
