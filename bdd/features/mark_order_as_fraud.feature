Feature:
  After a fraud case is confirmed in Salesforce,
  Paella needs to mark the order as fraud
  and check if the conditions are met and then call borscht to initiate the fraud reclaim action.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1

  Scenario: Try to mark a non-existent order as fraud
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "XF43Y" has UUID "test-uuid"
    When I send a POST request to "/order/test-wrong-uuid/mark-as-fraud"
    Then the response status code should be 404
    And print last JSON response
    And the JSON response should be:
        """
        {
            "error": "Order with UUID: test-wrong-uuid not found"
        }
        """

  Scenario: Try to mark an order as fraud that was already marked
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "XF43Y" has UUID "test-uuid"
    And The order "XF43Y" was already marked as fraud
    When I send a POST request to "/order/test-uuid/mark-as-fraud"
    Then the response status code should be 403
    And print last JSON response
    And the JSON response should be:
        """
        {
            "error": "Order was marked as fraud"
        }
        """

  Scenario: Successfully mark an order as fraud
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "XF43Y" has UUID "test-uuid"
    When I send a POST request to "/order/test-uuid/mark-as-fraud"
    Then the response status code should be 200
    And The order "XF43Y" is marked as fraud
