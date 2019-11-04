Feature: Create a new merchant.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Failed to create a merchant - company doesn't exist
    Given I get from companies service "/debtor/1" endpoint response with status 404 and body
      """
      {}
      """
    When I send a POST request to "/private/merchant" with body:
      """
      {
        "company_id": "1",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "debtor_financing_limit": 700.77,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key"
      }
      """
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Merchant company with the given ID was not found or couldn't be retrieved","code":"resource_not_found"}]}
    """

  Scenario: Failed to create a merchant - a merchant already exists with the same company ID
    Given a merchant exists with company ID 1
    When I send a POST request to "/private/merchant" with body:
      """
      {
        "company_id": "1",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "debtor_financing_limit": 700.77,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key"
      }
      """
    Then the response status code should be 409
    And the JSON response should be:
    """
    {"errors":[{"title":"Merchant with the same company ID already exists","code":"operation_failed"}]}
    """

  Scenario: Failed to create a merchant - Failed to create OAuth client
    Given I get from companies service get debtor response
    And I get from OAuth service "/clients" endpoint response with status 500 and body:
    """
    """
    When I send a POST request to "/private/merchant" with body:
      """
      {
        "company_id": "1",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "debtor_financing_limit": 700.77,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: key"
      }
      """
    Then the response status code should be 503
    And the JSON response should be:
    """
    {"errors":[{"title":"Failed to create OAuth client for merchant","code":"service_unavailable"}]}
    """

  Scenario: Successfully create a merchant
    Given I get from companies service get debtor response
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
    When I send a POST request to "/private/merchant" with body:
      """
      {
        "company_id": "1",
        "merchant_financing_limit": 5000.44,
        "initial_debtor_financing_limit": 500.00,
        "debtor_financing_limit": 700.77,
        "webhook_url": "http://billie.md",
        "webhook_authorization": "X-Api-Key: Hola"
      }
      """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {
      "name": "Test User Company",
      "financing_power": 5000.44,
      "financing_limit": 5000.44,
      "api_key": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4",
      "company_id": "1",
      "payment_merchant_id": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4",
      "is_active": true,
      "webhook_url": "http://billie.md",
      "webhook_authorization": "X-Api-Key: Hola",
      "oauth_client_id": "testClientId",
      "oauth_client_secret": "testClientSecret"
    }
    """
    And the default risk check setting should be created for merchant with company ID 1
    And the default notification settings should be created for merchant with company ID 1
    And all the default roles should be created for merchant with company ID 1
