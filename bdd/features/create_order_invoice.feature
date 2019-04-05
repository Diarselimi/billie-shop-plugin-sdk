Feature: Attach invoice file to an order.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"

  Scenario: Successfully create order invoice file
    When I send a POST request to "/order/CO123/invoice" with body:
      """
      {
          "file_id": "1299",
          "invoice_number": "BI1515"
      }
      """
    Then the response status code should be 201
    And the JSON response should be:
      """
      {
      }
      """

  Scenario: Order not found
    When I send a POST request to "/order/CO123wrong/invoice" with body:
      """
      {
          "file_id": "1299",
          "invoice_number": "BI1515"
      }
      """
    Then the response status code should be 404
    And the JSON response should be:
      """
      {
          "code": "not_found",
          "error": "Order #CO123wrong not found"
      }
      """
