Feature:
  In order to add a merchant debtor to whitelist
  I call to put the merchant debtor whitelist endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Successfully create Debtor Settings and mark as Whitelisted
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/private/debtors/c7be46c0-e049-4312-b274-258ec5aeeb70/whitelist" with body:
    """
      {"is_whitelisted": true}
    """
    Then the response status code should be 204
    And the debtor company with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" should be whitelisted
