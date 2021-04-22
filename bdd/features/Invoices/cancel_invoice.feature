Feature: Cancel Invoice feature

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission CANCEL_INVOICES

  Scenario: Cancel an invoice successfully
    Given I have a shipped order "ABCDE" with amounts 1000/900/100, duration 30 and checkout session "208cfe7d-046f-4162-b175-748942d6cff2"
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a DELETE request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4"
    Then the response status code should be 204
    And queue should contain message with routing key credit_note.create_credit_note with below data:
    """
    {
      "uuid": "@string@",
      "invoiceUuid": "208cfe7d-046f-4162-b175-748942d6cff4",
      "grossAmount": "6833",
      "netAmount": "7333",
      "externalComment": "",
      "internalComment": "cancelation",
      "externalCode": "some_code-CN"
    }
    """
