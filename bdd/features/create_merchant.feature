Feature: Create a new merchant.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Failed to create a merchant - company doesn't exist
    Given I get from companies service "/debtor/1" endpoint response with status 404 and body
      """
      {}
      """
    When I send a POST request to "/merchant" with body:
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
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "error": "Company with the given ID was not found or could't be retrieved"
    }
    """

  Scenario: Failed to create a merchant - a merchant already exists with the same company ID
    Given a merchant exists with company ID 1
    When I send a POST request to "/merchant" with body:
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
    {
      "error": "Merchant with the same company ID already exists"
    }
    """

  Scenario: Failed to create a merchant - Failed to create OAuth client
    Given I get from companies service get debtor response
    And I get from OAuth service "/clients" endpoint response with status 500 and body:
    """
    """
    When I send a POST request to "/merchant" with body:
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
    Then the response status code should be 500
    And the JSON response should be:
    """
    {
      "error": "Failed to create OAuth client for merchant"
    }
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
    When I send a POST request to "/merchant" with body:
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
    And the JSON response should have "id"
    And the JSON response at "payment_merchant_id" should be an string
    And I keep the JSON response as "MERCHANT"
    And the default risk check setting should be created with "{$MERCHANT}"
