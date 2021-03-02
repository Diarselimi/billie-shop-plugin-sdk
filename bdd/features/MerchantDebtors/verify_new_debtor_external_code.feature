Feature:
    In order to verify new debtor external code
    I call the verify new debtor external code endpoint

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "Authorization" header equal to "Bearer someToken"
        And I get from Oauth service a valid user token
        And a merchant user exists with permission CREATE_ORDERS

    Scenario: Sucessfully verify new debtor external code
        When I send a GET request to "/merchant/verify-new-external-code/ext-code"
        Then the response status code should be 204
