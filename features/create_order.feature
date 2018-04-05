Feature:
    In order to create an order
    I send the order data to the endpoint
    And expect empty response

    Scenario: Successful order creation
        Given I add "X-Api-User" header equal to "1"
        And I add "Content-type" header equal to "application/json"
        When I send a POST request to "/order" with body:
        And I have order with amount 100 and code 100 and duration 100 and amount 1000
        And I have customer with api key 100 and name contorion
        """
        {
          "duration": 50,
          "comment": "test",
          "external_code": "A1",
          "amount_net": "900",
          "amount_gross": "1000",
          "amount_tax": "100",
          "delivery_address_addition": "ext.21",
          "delivery_address_house_number": 52,
          "delivery_address_city": "Berlin",
          "delivery_address_postal_code": "10999",
          "delivery_address_country": "DE",
          "delivery_address_street": "Aachnerstr.",
          "debtor_company_merchant_customer_id": "XX12",
          "debtor_company_name": "Alex GmbH",
          "debtor_company_tax_id": "456",
          "debtor_company_tax_number": "456",
          "debtor_company_registration_number": "456",
          "debtor_company_registration_court": "456",
          "debtor_company_legal_form": "456",
          "debtor_company_industry_sector": "456",
          "debtor_company_subindustry_sector": "456",
          "debtor_company_employees_number": "456",
          "debtor_company_established_customer": true,
          "debtor_company_address_addition": "asad",
          "debtor_company_address_house_number": 52,
          "debtor_company_address_city": "Berlin",
          "debtor_company_address_postal_code": "10999",
          "debtor_company_address_country": "DE",
          "debtor_company_address_street": "Aachnerstr.",
          "debtor_person_gender": "m",
          "debtor_person_first_name": "David",
          "debtor_person_last_name": "Breva",
          "debtor_person_phone_number": "015777",
          "debtor_person_email": "david@divad"
        }
        """
        Then the response status code should be 201
        And the response should be empty
