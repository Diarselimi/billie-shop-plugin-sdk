Feature: I should be able to save the selected Signatory Powers that I send to paella

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission MANAGE_ONBOARDING


  Scenario: If I send a valid request and I get back the good response from companies service
    When I send a POST request to "/merchant/signatory-powers-selection" with body:
    """
    {
      "signatory_powers": [
        {
          "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4",
          "is_identified_as_user": false,
          "email": "billie@dev.io"
        },
        {
          "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce3",
          "is_identified_as_user": true,
          "email": "dev@billie.io"
        }

      ]
    }
    """
    And the response status code should be 204

  Scenario: If I send a valid request and I get back the good response from companies service
    When I send a POST request to "/merchant/signatory-powers-selection" with body:
    """
    {
      "signatory_powers": [
        {
          "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4",
          "is_identified_as_user": true,
          "email": "billie@dev.io"
        },
        {
          "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce3",
          "is_identified_as_user": true,
          "email": "dev@billie.io"
        }

      ]
    }
    """
    And the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"title":"There can be one or no users selected as current user.","code":"request_validation_error","source":"signatory_powers"}]}
    """

  Scenario: If I send a valid request and I get back the good response from companies service
    When I send a POST request to "/merchant/signatory-powers-selection" with body:
    """
    {
      "signatory_powers": [
        {
          "email": ""
        },
        {
          "uuid": "123123123123123",
          "is_identified_as_user": false,
          "email": "123asdas"
        }

      ]
    }
    """
    And the response status code should be 400

  Scenario: Having a non valid request we should get a validation error with the proper message if the request is empty
    When I send a POST request to "/merchant/signatory-powers-selection" with body:
    """
    """
    And the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"title":"At least one signatory should exist in request.","code":"request_validation_error","source":"signatory_powers"}]}
    """
