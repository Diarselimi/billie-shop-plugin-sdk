Feature:
    I want to authenticate as a user and receive a session_id
    In order to use that to create one order
    And I expect empty response

    Background:
      Given I add "Content-type" header equal to "application/json"
      And I add "X-Test" header equal to 1
      And I add "X-Api-Key" header equal to test

    Scenario: When i send a request to the checkout with the external id I should receive another id for use.
      When I send a POST request to "/checkout-session" with body:
      """
      {"merchant_customer_id": "1"}
      """
      Then the json response should have "id"
      And the response status code should be 200

    Scenario: When I don't provide a body should throw a form validation error
      When I send a POST request to "/checkout-session"
      Then the response should contain "This value should not be blank."
      And the response should contain "merchant_customer_id"
      And the response status code should be 400
