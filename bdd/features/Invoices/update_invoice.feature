Feature: Update invoices data

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission UPDATE_INVOICES

  Scenario: Update invoice data by calling the endpoint, a message should be triggered.
    Given I have a new order "ABCDE" with amounts 1000/900/100, duration 30 and checkout session "208cfe7d-046f-4162-b175-748942d6cff2"
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/update-details" with body:
    """
    {
      "external_code": "EXTERNA_CODE",
      "invoice_url": "https://billie.io"
    }
    """
    Then the response status code should be 204
    And the response should be empty
    And queue should contain message with routing key invoice.extend_invoice
