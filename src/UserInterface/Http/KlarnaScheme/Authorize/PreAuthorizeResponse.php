<?php

namespace App\UserInterface\Http\KlarnaScheme\Authorize;

use App\Support\StreetHouseParser;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Ozean12\Money\Money;

class PreAuthorizeResponse extends KlarnaResponse
{
    public function __construct(array $requestData, int $duration)
    {
        parent::__construct([
            'result' => 'user_action_required',
            'payment_method' => [
                'ui' => [
                    'data' => [ // all the mount data for widget should go here
                        'amount' => $this->mapAmount($requestData['amount'], $requestData['tax_amount'] ?? 0),
                        'duration' => $duration,
                        'delivery_address' => $this->mapDeliveryAddress($requestData),
                        'debtor_company' => $this->mapDebtorCompany($requestData),
                        'debtor_person' => $this->mapDebtorPerson($requestData),
                        'line_items' => $this->mapLineItems($requestData),
                    ],
                ],
            ],
        ]);
    }

    private function mapAmount(int $gross, int $tax): array
    {
        $grossMoney = new Money($gross, 2);
        $taxMoney = new Money($tax, 2);

        return [
            'gross' => $grossMoney->toFloat(),
            'net' => $grossMoney->subtract($taxMoney)->toFloat(),
            'tax' => $taxMoney->toFloat(),
        ];
    }

    private function mapDeliveryAddress(array $requestData): array
    {
        if (empty($requestData['purchase_details']['shipping_address'])) {
            return [];
        }

        $shippingData = $requestData['purchase_details']['shipping_address'];
        $streetHouse = sprintf('%s %s', $shippingData['street_address'] ?? '', $shippingData['street_address2'] ?? '');

        if (empty($shippingData['city']) || empty($shippingData['postal_code']) || empty($shippingData['country']) || empty(trim($streetHouse))) {
            return [];
        }

        [$street, $house] = StreetHouseParser::extractStreetAndHouse($streetHouse);

        return [
            'street' => $street,
            'house_number' => $house,
            'addition' => '',
            'city' => $shippingData['city'] ?? '',
            'postal_code' => $shippingData['postal_code'] ?? '',
            'country' => $shippingData['country'] ?? '',
        ];
    }

    private function mapDebtorCompany(array $requestData): array
    {
        if (empty($requestData['customer']['billing_address'])) {
            return [];
        }

        $address = $requestData['customer']['billing_address'];
        $streetHouse = sprintf('%s %s', $address['street_address'] ?? '', $address['street_address2'] ?? '');
        [$street, $house] = StreetHouseParser::extractStreetAndHouse($streetHouse);

        return [
            'name' => $address['organization_name'],
            'address_street' => $street,
            'address_house_number' => $house,
            'address_addition' => '',
            'address_city' => $address['city'],
            'address_postal_code' => $address['postal_code'],
            'address_country' => $address['country'],
        ];
    }

    private function mapDebtorPerson(array $requestData): array
    {
        if (empty($requestData['customer']['billing_address'])) {
            return [];
        }

        $address = $requestData['customer']['billing_address'];

        return [
            'first_name' => $address['given_name'] ?? '',
            'last_name' => $address['family_name'] ?? '',
            'phone_number' => $address['phone'] ?? '',
            'email' => $address['email'] ?? '',
        ];
    }

    private function mapLineItems(array $requestData): array
    {
        return array_map(function (array $lineItem) {
            return [
                'external_id' => $lineItem['reference'],
                'title' => $lineItem['name'],
                'description' => '',
                'quantity' => $lineItem['quantity'],
                'category' => $lineItem['product_identifiers']['category_path'] ?? '',
                'brand' => $lineItem['product_identifiers']['brand'] ?? '',
                'gtin' => $lineItem['product_identifiers']['global_trade_item_number'] ?? '',
                'mpn' => $lineItem['product_identifiers']['manufacturer_part_number'] ?? '',
                'amount' => $this->mapAmount($lineItem['total_amount'], $lineItem['total_tax_amount'] ?? 0),
            ];
        }, $requestData['purchase_details']['order_lines']);
    }
}
