Feature:
  In order to retrieve the (compact) order details
  I want to call the get order endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | debtor_is_trusted         |
      | limit                     |
      | debtor_not_customer       |
      | company_b2b_score         |
      | line_items                |
      | fraud_score               |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | line_items                | 1       | 1                  |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 0                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | fraud_score               | 1       | 0                  |
    And I get from Banco service search bank good response

  Scenario: Successful order retrieval
    Given I have orders with the following data
      | uuid                                 | external_id | state    | gross | net | tax | duration | comment    | payment_uuid                         |
      | 72611a94-aff1-4f71-8864-2bc00264d650 | XF43Y       | complete | 1000  | 900 | 100 | 30       | test order | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And I get from invoice-butler service good response no CreditNotes
    And I get from payments service get order details response
    And I get from Sepa service get mandate valid response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And GraphQL will respond to getMerchantDebtorDetails query
    When I send a GET request to "/private/compact/orders/72611a94-aff1-4f71-8864-2bc00264d650"
    Then the response status code should be 200
    And the JSON response should be:
    """
{
  "order": {
    "uuid": "72611a94-aff1-4f71-8864-2bc00264d650",
    "external_code": "XF43Y",
    "state": "complete",
    "amount": 1000,
    "workflow_name": "order_v1",
    "created_at": "2019-05-20 13:00:00"
  },
  "invoices": [
    {
      "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
      "payment_uuid": "8e6a9efa-3a76-44f1-ad98-24f0ef15d7ad",
      "external_code": "some_code",
      "state": "new",
      "amount": 123.33,
      "outstanding_amount": 50,
      "duration": 30,
      "due_date": "2020-12-26",
      "created_at": "2020-10-12 12:12:12"
    }
  ],
  "merchant": {
    "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
    "payment_uuid": "f2ec4d5e-79f4-40d6-b411-31174b6519ac",
    "name": "Behat Merchant"
  },
  "debtor": {
    "uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
    "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
    "payment_uuid": "9e06e31b-eb65-4e1e-9c96-2f3fc40f4bad",
    "name": "Test User Company"
  },
  "buyer": {
    "first_name": "test",
    "last_name": "test",
    "email": "test@ozean12.com"
  }
}
    """
