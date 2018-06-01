Feature:
    In order to create an order
    I send the order data to the endpoint
    And expect empty response

    Scenario: Debtor identification failed
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I get from risky "/risk-check/order/order_amount" endpoint response with status 200 and body
        """
        {
            "check_id": 1,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_country" endpoint response with status 200 and body
        """
        {
            "check_id": 2,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_industry_sector" endpoint response with status 200 and body
        """
        {
            "check_id": 3,
            "passed": true
        }
        """
        And I get from alfred "/debtor/identify" endpoint response with status 404 and body
        """
        """
        When I send a POST request to "/order" with body:
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
          "merchant_customer_id": "XX12",
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
        And print last JSON response
        And the response status code should be 403
        And the JSON response should be:
        """
        {
            "code":"order_rejected",
            "message":"debtor couldn\u0027t be identified"
        }
        """

    Scenario: Successful order creation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I get from risky "/risk-check/order/order_amount" endpoint response with status 200 and body
        """
        {
            "check_id": 1,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_country" endpoint response with status 200 and body
        """
        {
            "check_id": 2,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_industry_sector" endpoint response with status 200 and body
        """
        {
            "check_id": 3,
            "passed": true
        }
        """
        And I get from alfred "/debtor/identify" endpoint response with status 200 and body
        """
        {
          "id": 4,
          "payment_id": "test",
          "name": "Test User Company",
          "address_house": "10",
          "address_street": "Heinrich-Heine-Platz",
          "address_city": "Berlin",
          "address_postal_code": "10179",
          "address_country": "DE",
          "address_addition": null
        }
        """
        When I send a POST request to "/order" with body:
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
          "merchant_customer_id": "XX12",
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
        Then print last response
        And the response status code should be 201
        And the response should be empty

    Scenario: Debtor overdue check failed
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I get from risky "/risk-check/order/order_amount" endpoint response with status 200 and body
        """
        {
            "check_id": 1,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_country" endpoint response with status 200 and body
        """
        {
            "check_id": 2,
            "passed": true
        }
        """
        And I get from risky "/risk-check/order/order_debtor_industry_sector" endpoint response with status 200 and body
        """
        {
            "check_id": 3,
            "passed": true
        }
        """
        And I get from alfred "/debtor/identify" endpoint response with status 200 and body
        """
        {
          "id": 4,
          "payment_id": "test",
          "name": "Test User Company",
          "address_house": "10",
          "address_street": "Heinrich-Heine-Platz",
          "address_city": "Berlin",
          "address_postal_code": "10179",
          "address_country": "DE",
          "address_addition": null
        }
        """
        And I have a late order XLO123 with amounts 1002/901/101, duration 30 and comment "test order"
        And Order XLO123 was shipped at "2018-01-01 00:00:00"
        When I send a POST request to "/order" with body:
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
          "merchant_customer_id": "XX12",
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
        Then print last response
        And the response status code should be 403
        And the JSON response should be:
        """
        {
            "code":"order_rejected",
            "message":"checks failed"
        }
        """
