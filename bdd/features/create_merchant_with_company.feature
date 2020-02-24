Feature: Create a new merchant specifying the data of the company that will be created and associated to it.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Failed to create a merchant - provided data is wrong data
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "merchant_financing_limit": "x2",
        "initial_debtor_financing_limit": "abc",
        "webhook_url": "foobar",
        "webhook_authorization": "X-Api-Key: key",
        "is_onboarding_complete": 2
      }
      """
    Then the response status code should be 400
    And the JSON response should be:
      """
      {
        "errors":[
          {
            "title":"The number should have have maximum 2 numbers after decimal.",
            "code":"request_validation_error",
            "source":"merchant_financing_limit"
          },
          {
            "title":"The number should have have maximum 2 numbers after decimal.",
            "code":"request_validation_error",
            "source":"initial_debtor_financing_limit"
          },
          {
            "title":"This value is not a valid URL.",
            "code":"request_validation_error",
            "source":"webhook_url"
          },
          {
            "title":"This value should be of type bool.",
            "code":"request_validation_error",
            "source":"is_onboarding_complete"
          },
          {
            "title":"This value should not be blank.",
            "code":"request_validation_error",
            "source":"iban"
          },
          {
            "title":"This value should not be blank.",
            "code":"request_validation_error",
            "source":"bic"
          }
        ]
      }
      """

  Scenario: Failed to create a merchant - call to Companies Service failed with 400
    Given I get from companies service "/debtors" endpoint response with status 400 and body
      """
      {
        "error": "Invalid data provided",
        "code": 400,
        "properties": [
          {
            "name": "name",
            "message": "This value should not be blank."
          },
          {
            "name": "legal_form",
            "message": "This value should not be blank."
          },
          {
            "name": "address_street",
            "message": "This value should not be blank."
          },
          {
            "name": "address_city",
            "message": "This value should not be blank."
          },
          {
            "name": "address_postal_code",
            "message": "This value should not be blank."
          },
          {
            "name": "address_country",
            "message": "This value should not be blank."
          },
          {
            "name": "crefo_id",
            "message": "One of crefo_id or schufa_id should be provided, but both are empty."
          },
          {
            "name": "schufa_id",
            "message": "One of crefo_id or schufa_id should be provided, but both are empty."
          }
        ]
      }
      """
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key",
        "is_onboarding_complete": false
      }
      """
    Then the response status code should be 400
    And the JSON response should be:
      """
      {
        "errors":[
          {
             "title":"This value should not be blank.",
             "code":"request_validation_error",
             "source":"iban"
          },
          {
             "title":"This value should not be blank.",
             "code":"request_validation_error",
             "source":"bic"
          }
        ]
      }
      """

  Scenario: Failed to create a merchant - call to Companies Service failed
    Given I get from companies service "/debtors" endpoint response with status 500 and body
      """
      {"error": "Something went wrong"}
      """
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "name": "Gunny GmbH",
        "legal_form": "GmbH",
        "address_street": "Charlottenstr.",
        "address_house": "7",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "crefo_id": "crefo123",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key",
        "is_onboarding_complete": false,
        "iban": "DE87500105173872482875",
        "bic": "AABSDE31"
      }
      """
    Then the response status code should be 503
    And the JSON response should be:
      """
      {"errors":[{"title":"Merchant company creation failed.","code":"service_unavailable"}]}
      """

  Scenario: Failed to create a merchant - a merchant already exists with the same company ID
    Given a merchant exists with company ID 1
    And I successfully create OAuth client with id testClientId and secret testClientSecret
    And I get from companies service a successful response on create debtor call with body:
      """
      {
        "id": 1,
        "uuid": "3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
        "crefo_id": "crefo123",
        "schufa_id": null,
        "google_places_id": null,
        "name": "Gunny GmbH",
        "address_house": "7",
        "address_street": "Charlottenstr.",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "address_addition": null,
        "is_blacklisted": false,
        "is_from_trusted_source": true
      }
      """
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "name": "Gunny GmbH",
        "legal_form": "GmbH",
        "address_street": "Charlottenstr.",
        "address_house": "7",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "crefo_id": "crefo123",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key",
        "is_onboarding_complete": false,
        "iban": "DE87500105173872482875",
        "bic": "AABSDE31"
      }
      """
    Then the response status code should be 409
    And the JSON response should be:
      """
      {"errors":[{"title":"Merchant with the same company ID already exists","code":"resource_already_exists"}]}
      """

  Scenario: Failed to create a merchant - Failed to create OAuth client
    Given I get from OAuth service "/clients" endpoint response with status 500 and body:
      """
      """
    And I get from companies service a successful response on create debtor call with body:
      """
      {
        "id": 1,
        "uuid": "3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
        "crefo_id": "crefo123",
        "schufa_id": null,
        "google_places_id": null,
        "name": "Gunny GmbH",
        "address_house": "7",
        "address_street": "Charlottenstr.",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "address_addition": null,
        "is_blacklisted": false,
        "is_from_trusted_source": true
      }
      """
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "name": "Gunny GmbH",
        "legal_form": "GmbH",
        "address_street": "Charlottenstr.",
        "address_house": "7",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "crefo_id": "crefo123",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key",
        "is_onboarding_complete": false,
        "iban": "DE87500105173872482875",
        "bic": "AABSDE31"
      }
      """
    Then the response status code should be 503
    And the JSON response should be:
      """
      {"errors":[{"title":"Failed to create OAuth client for merchant","code":"service_unavailable"}]}
      """

  Scenario: Successfully create a merchant
    Given I get from companies service a successful response on create debtor call with body:
      """
      {
        "id": 1,
        "uuid": "3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
        "crefo_id": "crefo123",
        "schufa_id": null,
        "google_places_id": null,
        "name": "Gunny GmbH",
        "address_house": "7",
        "address_street": "Charlottenstr.",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "address_addition": null,
        "is_blacklisted": false,
        "is_from_trusted_source": true
      }
      """
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
    And I successfully create OAuth client with id testClientId and secret testClientSecret
    And I get from limit service create default debtor-customer limit successful response
    When I send a POST request to "/private/merchant/with-company" with body:
      """
      {
        "name": "Gunny GmbH",
        "legal_form": "GmbH",
        "address_street": "Charlottenstr.",
        "address_house": "7",
        "address_city": "Berlin",
        "address_postal_code": "10969",
        "address_country": "DE",
        "crefo_id": "crefo123",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: hola",
        "is_onboarding_complete": false,
        "iban": "DE87500105173872482875",
        "bic": "AABSDE31"
      }
      """
    Then the response status code should be 201
    And the JSON response should be:
      """
      {
         "id":2,
         "name":"Gunny GmbH",
         "financing_power":5000.44,
         "financing_limit":5000.44,
         "company_id":"1",
         "company_uuid":"3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
         "payment_merchant_id":"f90e2969-4c42-4003-8d2e-0f3cc6082ab6",
         "is_active":true,
         "webhook_url":"http:\/\/billie.md",
         "webhook_authorization":"X-Api-Key: hola",
         "created_at":"2020-02-17 14:27:32",
         "updated_at":"2020-02-17 14:27:32",
         "oauth_client_id":"testClientId",
         "oauth_client_secret":"testClientSecret"
      }
      """
    And the JSON should have "api_key"
    And the JSON should have "payment_merchant_id"
    And the default risk check setting should be created for merchant with company ID 1
    And the default notification settings should be created for merchant with company ID 1
    And all the default roles should be created for merchant with company ID 1
