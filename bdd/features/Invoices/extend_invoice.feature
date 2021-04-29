Feature: Extend invoice duration

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Extending invoice duration
    Given I have orders with the following data
      |id| duration | state   | gross | net | tax | duration | comment    | workflow_name |
      | 1| 30       | shipped | 1000  | 900 | 100 | 30       | test order | order_v2      |
    And I get from invoice-butler service an invoice that can be extended
    And I get from payments service modify ticket response
    And I get from payments service get order detail response
    And Salesforce DCI API responded for the order UUID 'test-order-uuid' with no collections taking place
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/extend-duration" with body:
        """
        {
          "duration": 120
        }
        """
    Then the response status code should be 204
    And the response should be empty
    And queue should contain message with routing key invoice.extend_invoice with below data:
    """
    {
      "invoice": {
        "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
        "dueDate": "@string@",
        "netFeeAmount": 840,
        "vatOnFeeAmount": 159,
        "feeRate": 100,
        "invoiceReferences": {
          "external_code": "@string@"
        },
        "duration": 120
      }
    }
    """
