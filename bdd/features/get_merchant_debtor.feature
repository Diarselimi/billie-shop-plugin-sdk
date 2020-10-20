Feature:
  In order to retrieve the merchant debtor details
  I call the get merchant debtor endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Get merchant debtor details
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I add "X-Api-Key" header equal to test
    When I send a GET request to "/public/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "id":"ad74bbc4-509e-47d5-9b50-a0320ce3d715",
        "external_code":"ext_id",
        "name":"Test User Company",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_house":"10",
        "address_city":"Berlin",
        "address_country":"DE",
        "financing_limit":7500,
        "financing_power":4500,
        "outstanding_amount":500,
        "outstanding_amount_created":800,
        "outstanding_amount_late":0,
        "bank_account_iban":"DE1234",
        "bank_account_bic":"BICISHERE",
        "debtor_information_change_request_state":null,
        "debtor_information_change_request":null,
        "legal_form":"GmbH"
    }
    """

  Scenario: Get merchant debtor details, extended for support
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from payments service get debtor response
    And I get from companies service get debtor response
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    When I send a GET request to "/private/merchant-debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "id":"ad74bbc4-509e-47d5-9b50-a0320ce3d715",
        "external_code":"ext_id",
        "name":"Test User Company",
        "financing_limit":7500,
        "financing_power":4500,
        "created_at":"2019-01-01T12:00:00+0100",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":"10",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE",
        "outstanding_amount":500,
        "outstanding_amount_created":800,
        "outstanding_amount_late":0,
        "debtor_information_change_request_state":null,
        "debtor_information_change_request":null,
        "legal_form":"GmbH",
        "bank_account_iban":"DE1234",
        "bank_account_bic":"BICISHERE",
        "merchant_debtor_id":1,
        "company_id":1,
        "company_uuid":"c7be46c0-e049-4312-b274-258ec5aeeb70",
        "payment_id":"test",
        "is_blacklisted":false,
        "is_trusted_source":true,
        "crefo_id":"123",
        "schufa_id":"123"
    }
    """

  Scenario: Empty limits returned when call to limits service unsuccessful
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from payments service get debtor response
    And I get from companies service get debtor response
    And I get from limit service get debtor limit unsuccessful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I add "X-Api-Key" header equal to test
    When I send a GET request to "/public/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "id":"ad74bbc4-509e-47d5-9b50-a0320ce3d715",
      "external_code":"ext_id",
      "name":"Test User Company",
      "address_street":"Heinrich-Heine-Platz",
      "address_postal_code":"10179",
      "address_house":"10",
      "address_city":"Berlin",
      "address_country":"DE",
      "financing_limit":null,
      "financing_power":null,
      "outstanding_amount":500,
      "outstanding_amount_created":800,
      "outstanding_amount_late":0,
      "bank_account_iban":"DE1234",
      "bank_account_bic":"BICISHERE",
      "debtor_information_change_request_state":null,
      "debtor_information_change_request":null,
      "legal_form":"GmbH"
    }
    """
