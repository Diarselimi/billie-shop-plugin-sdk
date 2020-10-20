<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

class GraphQLContext implements Context
{
    use MockServerTrait;

    /**
     * @Given GraphQL will respond to :query with :statusCode and response:
     */
    public function graphQLWillRespondToWithAndResponse($query, $statusCode, PyStringNode $response)
    {
        $this->mockRequestWith('', (string) $response, ['json' => $query], (int) $statusCode);
    }

    /**
     * @Given GraphQL will respond to :query with :statusCode and responses:
     */
    public function graphQLWillRespondToWithAndResponses($query, $statusCode, PyStringNode $responses)
    {
        $responses = json_decode((string) $responses, true);
        $this->mockRequestWithResponseStack('', $responses, ['json' => $query], (int) $statusCode);
    }

    /**
     * @Given GraphQL will respond to getMerchantDebtorDetails query
     */
    public function graphQLWillRespondToGetMerchantDebtorDetailsQuery()
    {
        $response = <<<EOF
{
  "data": {
    "getMerchantDebtorDetails": [
      {
        "company_id": "1",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "name": "Test User Company",
        "house_number": "10",
        "street": "Heinrich-Heine-Platz",
        "city": "Berlin",
        "postal_code": "10179",
        "country": "DE",
        "schufa_id": "123",
        "crefo_id": "123",
        "iban": "DE1234",
        "bic": "BICISHERE"
      }
    ]
  }
}
EOF;

        $this->mockRequestWith('', $response, ['json' => 'getMerchantDebtorDetails'], 200);
    }
}
