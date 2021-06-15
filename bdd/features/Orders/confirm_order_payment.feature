Feature: Confirm order payments
  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Successful payment confirmation
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/order/CO123/confirm-payment" with body:
    """
    {
      "paid_amount": 1000
    }
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: Unsuccessful confirm order payment when missing paid_amount
    When I send a POST request to "/order/CO123/confirm-payment" with body:
    """
    {
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "source": "paid_amount",
          "title": "This value should not be blank.",
          "code": "request_validation_error"
        }
      ]
    }
    """

  Scenario: Unsuccessful payment confirmation
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/CO123/confirm-payment" with body:
    """
    {
      "paid_amount": "NOT AN AMOUNT"
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"source":"paid_amount","title":"This value should be greater than 0.","code":"request_validation_error"}]}
    """

  Scenario: Try to confirm a non existing Order
    Given I send a POST request to "/order/NON-EXISTING/confirm-payment" with body:
    """
    {"paid_amount": 1000.00}
    """
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """
