Feature:
  Create order for dashboard

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And I get from payments service get debtor response
    And I get from Fraud service a non fraud response

  Scenario: Successful order creation
    Given I get from companies service identify match response
    And GraphQL will respond to getMerchantDebtorDetails query
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from payments service register debtor positive response
    When I send a POST request to "/public/order-dashboard" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@billie.io"
       },
       "debtor_company":{
          "merchant_customer_id":"12",
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_country":"DE",
          "tax_id":"VA222",
          "tax_number":"3333",
          "registration_court":"",
          "registration_number":" some number",
          "industry_sector":"some sector",
          "subindustry_sector":"some sub",
          "employees_number":"33",
          "legal_form":"some legal",
          "established_customer":1
       },
       "delivery_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "amount":{
          "net":900.00,
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state created
    And the order A1 has creation source "dashboard"
    And the response status code should be 200
    And the JSON response should be file "create_order_response.json"
    And the order "A1" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz 10 de"
