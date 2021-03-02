Feature:
  I need to synchronize the merchant debtor by providing the uuid in the url,
  and I will get the response back

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And I get from companies service synchronize merchant debtor good response and synchronized

  Scenario: Merchant debtor successfully updated from the external sources
    Given I have a created order with amounts 100/200/300, duration 30 and comment "test"
    And I send a POST request to "/private/merchant-debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/synchronize"
    Then the JSON response should be:
    """
    {
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "is_updated":true
    }
    """
    And the response status code should be 200

  Scenario: Merchant debtor fails to find the Merchant Debtor by Uuid
    When I send a POST request to "/private/merchant-debtor/non-existing-uuid/sync"
    Then the response status code should be 404


