Feature: Get list of signatory-powers from alfred and send response.

  Background: Something to happen
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ONBOARDING

  Scenario: Get a list of signatories with no match with the current logged in user
    Given I get from companies service a list of signatory-powers one signatory
    When I send a GET request to "/merchant/signatory-powers"
    And the response status code should be 200
    And the JSON response should be:
    """
    [
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "name",
        "last_name": "billie",
        "additional_signatories_required": 1,
        "address_house":"Any house number",
        "address_street":"Any street",
        "address_city":"Any city",
        "address_postal_code":"Any postal code",
        "address_country":"DE",
        "automatically_identified_as_user": false
      }
    ]
    """

  Scenario: Get a list of signatories with a match with the current logged in user
    And I get from companies service a list of signatory-powers
    When I send a GET request to "/merchant/signatory-powers"
    And the response status code should be 200
    And the JSON response should be:
    """
    [
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "name",
        "last_name": "billie",
        "additional_signatories_required": 1,
        "address_house":"Any house number",
        "address_street":"Any street",
        "address_city":"Any city",
        "address_postal_code":"Any postal code",
        "address_country":"DE",
        "automatically_identified_as_user": false
      },
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Test",
        "last_name": "Test",
        "additional_signatories_required": 1,
        "address_house":"Any house number",
        "address_street":"Any street",
        "address_city":"Any city",
        "address_postal_code":"Any postal code",
        "address_country":"DE",
        "automatically_identified_as_user": true
      }
    ]
    """


  Scenario: Get empty list of signatories
    Given I get from companies service a empty list of signatory-powers
    When I send a GET request to "/merchant/signatory-powers"
    And the response status code should be 200
    And the JSON response should be:
    """
    []
    """
