Feature:
  In order to add a merchant debtor to whitelist
  I call to put the merchant debtor whitelist endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Mark successfully Merchant Debtor as Whitelisted
    And I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from payments service get debtor response
    When I send a POST request to "/merchant-debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/whitelist" with body:
    """
      {"is_whitelisted": "1"}
    """
    Then the response status code should be 204
    And the merchant debtor "ext_id" with merchantId 1 should be whitelisted
