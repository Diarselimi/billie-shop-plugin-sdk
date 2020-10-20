<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\MerchantDebtor\Details\MerchantDebtorDetailsDTO;
use App\DomainModel\MerchantDebtor\Details\MerchantDebtorDetailsRepositoryInterface;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use Ozean12\GraphQLBundle\GraphQLInterface;

class MerchantDebtorDetailsGraphQLRepository extends AbstractGraphQLRepository implements MerchantDebtorDetailsRepositoryInterface
{
    private const GET_MERCHANT_DEBTOR_DETAILS_QUERY = 'get_merchant_debtor_details';

    private AddressEntityFactory $addressFactory;

    public function __construct(GraphQLInterface $graphQL, AddressEntityFactory $addressFactory)
    {
        parent::__construct($graphQL);

        $this->addressFactory = $addressFactory;
    }

    public function getMerchantDebtorDetails(int $merchantDebtorId): MerchantDebtorDetailsDTO
    {
        $params = [
            'merchant_debtor_id' => $merchantDebtorId,
        ];

        $response = $this->query(self::GET_MERCHANT_DEBTOR_DETAILS_QUERY, $params);

        if (!$response) {
            throw new GraphQLException('Merchant debtor not found in GraphQL');
        }

        $response = $response[0];

        return new MerchantDebtorDetailsDTO(
            (new Company())
                ->setId((int) $response['company_id'])
                ->setUuid($response['company_uuid'])
                ->setName($response['name'])
                ->setAddress($this->addressFactory->createDebtorCompanyAddressFromDatabaseRow($response))
                ->setSchufaId($response['schufa_id'])
                ->setCrefoId($response['crefo_id']),
            (new DebtorPaymentDetailsDTO())
                ->setBankAccountIban($response['iban'])
                ->setBankAccountBic($response['bic'])
        );
    }
}
