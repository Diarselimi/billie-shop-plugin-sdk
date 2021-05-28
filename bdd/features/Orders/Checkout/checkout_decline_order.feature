Feature:
  As a merchant user I want to be able to decline my checkout orders from the widget.
  Also declining the order will reactivate the session so that I can re-use it for authorising an order.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: I succeed to decline an order after I have it authorised.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And the checkout_session_id "123123CO123" should be invalid
    And I send a GET request to "/checkout-session/123123CO123/decline"
    Then the response status code should be 204
    And the checkout_session_id "123123CO123" should be valid
    And the order CO123 is in state declined
