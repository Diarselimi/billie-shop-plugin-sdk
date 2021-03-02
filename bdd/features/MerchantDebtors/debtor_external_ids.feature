Feature:
  Get debtor external ids

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Successfully get debtor external ids
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/public/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/external-ids"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 1,
      "items": ["ext_id"]
    }
    """
