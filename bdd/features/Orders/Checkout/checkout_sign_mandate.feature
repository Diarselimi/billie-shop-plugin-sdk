Feature:
  I want to be able to sign a mandate for a specific checkout order.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: I succeed to decline an order after I have it authorised.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123123"
    And the checkout_session_id "123123123" should be invalid
    And A sepa mandate already exists for order "CO123"
    And A sepa mandate sign call should be successful
    And I send a POST request to "/checkout-session/123123123/sign-mandate"
    Then the response status code should be 204
