Feature: Create Credit Note

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Successful credit note creation
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment    | workflow_name |
      | CO123       | shipped | 1000  | 900 | 100 | 30       | test order | order_v2      |
    And I get from invoice-butler service good response
    And I get from payments service modify ticket response
    And GraphQL will respond to getMerchantDebtorDetails query
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/credit-notes" with body:
        """
        {
          "amount": {"gross": 499.96, "net": 499.96, "tax": 0.0},
          "external_code": "ext-code",
          "comment": "ext-comment"
        }
        """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {
      "uuid": "uuid"
    }
    """
    And queue should contain message with routing key credit_note.create_credit_note with below data:
    """
    {
      "uuid": "@string@",
      "invoiceUuid": "208cfe7d-046f-4162-b175-748942d6cff4",
      "grossAmount": "49996",
      "netAmount": "49996",
      "externalComment": "ext-comment",
      "externalCode": "ext-code"
    }
    """
