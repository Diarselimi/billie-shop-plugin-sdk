Feature: As a merchant that is in the onboarding process, I want start the technical integration and create a sandbox merchant

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token

  Scenario: Successful integration start call
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And I get from sandbox service a successful response on "/api/merchant/with-company" call with body:
    """
      {
        "id": 123,
        "api_key": "4ecc796d-fb19-43bc-acdb-7d13defa3c96",
        "payment_merchant_id": "1a8e2f46-7467-4910-8d35-ef61bb7af08c",
        "name": "Gunny Sandbox GmbH",
        "financing_power": 5000.44,
        "financing_limit": 5000.44,
        "company_id": "2",
        "is_active": true,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: hola",
        "oauth_client_id": "testClientId",
        "oauth_client_secret": "testClientSecret"
      }
    """
    And I get from companies service "/debtor/10" endpoint response with status 200 and body
      """
      {
        "id": 10,
        "uuid": "857dbedf-4f3a-420a-b15f-26892b16dd67",
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
    And I get from companies service "/debtor/2" endpoint response with status 200 and body
      """
      {
        "id": 2,
        "uuid": "3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
        "crefo_id": "crefo123",
        "schufa_id": null,
        "google_places_id": null,
        "name": "Gunny Sandbox GmbH",
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
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 200
    And the JSON should have "production_credentials"
    And the JSON should not have "production_credentials/client_id"
    And the JSON should not have "production_credentials/client_secret"
    And the JSON should have "sandbox_credentials"
    And the JSON should have "sandbox_credentials/client_id"
    And the JSON should have "sandbox_credentials/client_secret"
    And the JSON at "sandbox_credentials/client_id" should be "testClientId"
    And the JSON at "sandbox_credentials/client_secret" should be "testClientSecret"
    And a merchant exists with company ID 10 and sandbox merchant payment UUID "1a8e2f46-7467-4910-8d35-ef61bb7af08c"

  Scenario: Start integration fails if step is not in state new
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And The following onboarding steps are in states for merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac":
      | name                        | state    |
      | financial_assessment        | new      |
      | signatory_confirmation      | new      |
      | identity_verification       | new      |
      | ubo_pepsanctions_assessment | new      |
      | technical_integration       | complete |
      | sepa_mandate_confirmation   | new      |
      | sales_confirmation          | new      |
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Start integration not allowed","code":"forbidden"}]}
    """

  Scenario: Start integration fails if Sandbox Payment UUID is already set
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And the sandbox merchant payment UUID is already set
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Start integration not allowed","code":"forbidden"}]}
    """

  Scenario: Start integration fails if Sandbox client is not available
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And I get from sandbox service a response on "/api/merchant/with-company" call with status code 500 body:
    """
      {"error": "unexpected error"}
    """
    And I get from companies service "/debtor/10" endpoint response with status 200 and body
      """
      {
        "id": 10,
        "uuid": "857dbedf-4f3a-420a-b15f-26892b16dd67",
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
    And I get from companies service "/debtor/2" endpoint response with status 200 and body
      """
      {
        "id": 2,
        "uuid": "3a88a67f-770c-4e2b-8d56-fba0ca003d6a",
        "crefo_id": "crefo123",
        "schufa_id": null,
        "google_places_id": null,
        "name": "Gunny Sandbox GmbH",
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
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 500
    And the JSON response should be:
    """
      {"errors":[{"title":"Integration cannot be started","code":"operation_failed"}]}
    """
