Feature: Confirm invoice payments

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
    When I send a POST request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/confirm-payment" with body:
        """
        {
          "paid_amount": 499.96
        }
        """
    Then the response status code should be 204
    And the response should be empty
