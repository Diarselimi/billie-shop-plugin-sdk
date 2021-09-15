Feature:
  In order to create an order
  I send the order data to the endpoint
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | delivery_address          |
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
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | delivery_address          | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | fraud_score               | 1       | 0                  |
    And I get from Fraud service a non fraud response
    And GraphQL will respond to getMerchantDebtorDetails query


  Scenario: Successful order creation
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from Banco service search bank good response
    And I get from payments service register debtor positive response
    When I send a POST request to "/public/api/v2/orders" with body:
    """
    {
       "amount": {
         "net":900.00,
         "gross":1000.00,
         "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "external_code": "A1",
       "delivery_address": {
         "house_number":"10",
         "street":"Heinrich-Heine-Platz",
         "city":"Berlin",
         "postal_code":"10179",
         "country":"DE"
       },
       "debtor": {
         "merchant_customer_id":"12",
         "name":"Test User Company",
         "tax_id":"VA222",
         "tax_number":"3333",
         "registration_court":"",
         "registration_number":" some number",
         "industry_sector":"some sector",
         "subindustry_sector":"some sub",
         "employees_number":"33",
         "legal_form":"some legal",
         "established_customer":1,
         "company_address": {
           "addition":"left door",
           "house_number":"10",
           "street":"Heinrich-Heine-Platz",
           "city":"Berlin",
           "postal_code":"10179",
           "country":"DE"
         },
         "billing_address": {
           "house_number":"10",
           "street":"Heinrich-Heine-Platz",
           "city":"Berlin",
           "postal_code":"10179",
           "country":"DE"
         }
       },
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@billie.io"
       },
      "line_items": []
    }
    """
    And the response status code should be 200
    Then the order A1 is in state created
    And the JSON response should be file "create_order_response_v2.json"
