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
}
