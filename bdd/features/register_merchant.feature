Feature: Register a new merchant with an invitation for the initial admin user.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Failed to create a merchant - company doesn't exist
    Given I get from companies service "/debtor/crefo/123" endpoint response with status 404 and body
      """
      {}
      """
    When I send a POST request to "/private/merchant/registration" with body:
      """
      {
        "crefo_id": "123",
        "email": "test@billie.dev"
      }
      """
    Then the JSON response should be:
      """
      {"errors":[{"title":"Cannot find a company with the given crefo ID","code":"resource_not_found"}]}
      """
    And the response status code should be 404

  Scenario: Failed to create a merchant - a merchant already exists with the same company ID
    Given a merchant exists with company ID 1
    And I get from companies service "/debtor/crefo/123" endpoint response with status 200 and body
    """
      {
        "total": 1,
        "items": [{
            "id": 1,
            "uuid": "07be46c0-e049-4312-b274-258ec5aeeb70",
            "crefo_id": "123",
            "schufa_id": "1234",
            "google_places_id": null,
            "name": "Gunny GmbH",
            "address_house": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Berlin",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_addition": null,
            "is_blacklisted": 0,
            "is_from_trusted_source": 1
        }]
      }
    """
    When I send a POST request to "/private/merchant/registration" with body:
      """
      {
        "crefo_id": "123",
        "email": "test@billie.dev"
      }
      """
    Then the JSON response should be:
      """
      {"errors":[{"title":"Merchant with the same company ID already exists","code":"resource_already_exists"}]}
      """
    And the response status code should be 409

  Scenario: Failed to create a merchant - Failed to create OAuth client
    Given I get from companies service "/debtor/crefo/123" endpoint response with status 200 and body
      """
        {
          "total": 1,
          "items": [{
              "id": 1,
              "uuid": "07be46c0-e049-4312-b274-258ec5aeeb70",
              "crefo_id": "123",
              "schufa_id": "1234",
              "google_places_id": null,
              "name": "Gunny GmbH",
              "address_house": "10",
              "address_street": "Heinrich-Heine-Platz",
              "address_city": "Berlin",
              "address_postal_code": "10179",
              "address_country": "DE",
              "address_addition": null,
              "is_blacklisted": 0,
              "is_from_trusted_source": 1
          }]
        }
      """
    And I get from OAuth service "/clients" endpoint response with status 500 and body:
      """
      {}
      """
    When I send a POST request to "/private/merchant/registration" with body:
      """
      {
        "crefo_id": "123",
        "email": "test@billie.dev"
      }
      """
    Then the JSON response should be:
      """
      {"errors":[{"title":"Failed to create OAuth client for merchant","code":"service_unavailable"}]}
      """
    And the response status code should be 503

  Scenario: Failed to create a merchant - there are many companies with same crefo ID
    Given I get from companies service "/debtor/crefo/123" endpoint response with status 200 and body
    """
      {
        "total": 2,
        "items": [{
            "id": 1,
            "uuid": "07be46c0-e049-4312-b274-258ec5aeeb70",
            "crefo_id": "123",
            "schufa_id": "1234",
            "google_places_id": null,
            "name": "Gunny GmbH",
            "address_house": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Berlin",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_addition": null,
            "is_blacklisted": 0,
            "is_from_trusted_source": 1
        },{
            "id": 2,
            "uuid": "17be46c0-e049-4312-b274-258ec5aeeb70",
            "crefo_id": "123",
            "schufa_id": "1234",
            "google_places_id": null,
            "name": "Gunny GmbH",
            "address_house": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Berlin",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_addition": null,
            "is_blacklisted": 0,
            "is_from_trusted_source": 1
        }]
      }
    """
    When I send a POST request to "/private/merchant/registration" with body:
      """
      {
        "crefo_id": "123",
        "email": "test@billie.dev"
      }
      """
    Then the JSON response should be:
      """
      {"errors":[{"title":"There are multiple companies with the same crefo ID","code":"resource_already_exists"}]}
      """
    And the response status code should be 409

  Scenario: Successfully create a merchant
    Given I get from companies service "/debtor/crefo/123" endpoint response with status 200 and body
      """
        {
          "total": 1,
          "items": [{
              "id": 1,
              "uuid": "07be46c0-e049-4312-b274-258ec5aeeb70",
              "crefo_id": "123",
              "schufa_id": "1234",
              "google_places_id": null,
              "name": "Gunny GmbH",
              "address_house": "10",
              "address_street": "Heinrich-Heine-Platz",
              "address_city": "Berlin",
              "address_postal_code": "10179",
              "address_country": "DE",
              "address_addition": null,
              "is_blacklisted": 0,
              "is_from_trusted_source": 1
          }]
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
    When I send a POST request to "/private/merchant/registration" with body:
      """
      {
        "crefo_id": "123",
        "email": "test@billie.dev"
      }
      """
    Then the JSON response should be:
      """
      {
        "name": "Gunny GmbH"
      }
      """
    And the response status code should be 201
    And the JSON should have "uuid"
    And the JSON should have "invitation_token"
    And the default risk check setting should be created for merchant with company ID 1
    And the default notification settings should be created for merchant with company ID 1
    And all the default roles should be created for merchant with company ID 1
    And a user invitation with role "admin" and email "test@billie.dev" should have been created for merchant with company ID 1
