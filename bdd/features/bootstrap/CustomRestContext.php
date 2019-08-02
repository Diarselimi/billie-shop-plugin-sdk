<?php

use Behat\Gherkin\Node\TableNode;
use Behatch\Context\BaseContext;
use Behatch\HttpCall\Request;
use Behatch\HttpCall\Request\Goutte;

class CustomRestContext extends BaseContext
{
    /**
     * @var Request|Goutte
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @When I send a :method request to :uri with query string:
     */
    public function iSendARequestWithQueryString($method, $uri, TableNode $query)
    {
        $parameters = [];

        foreach ($query->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }
            $parameters[$row['key']] = $row['value'];
        }

        $parameters_str = http_build_query($parameters);

        return $this->request->send(
            $method,
            $this->locatePath($uri) . '?' . $parameters_str,
            [],
            [],
            null
        );
    }
}
