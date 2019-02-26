Feature:
  Whenever SF updates Paella that a dunning step of an order has been updated, the merchant should be updated as well

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1

  Scenario: Update dunning step of non existing order
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "XF43Y" has UUID "test-uuid"
    When I send a POST request to "/order/test-wrong-uuid/update-dunning-step" with body:
        """
        {
            "step": "Dunning 1"
        }
        """
    Then the response status code should be 404
    And the JSON response should be:
      """
      {
          "error": "Order with UUID: test-wrong-uuid not found"
      }
      """

  Scenario: Successfully update merchant webhook with order dunning step
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "XF43Y" has UUID "test-uuid"
    When I send a POST request to "/order/test-uuid/update-dunning-step" with body:
        """
        {
            "step": "DCA Handover"
        }
        """
    Then the response status code should be 204
    And the response should be empty
