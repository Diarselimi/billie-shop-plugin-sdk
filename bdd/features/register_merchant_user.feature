Feature: Register merchant user to access dashboard

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: successfully register merchant user
    Given I successfully create OAuth client with email "test@merchantX.com" and user id "test-auth-id"
    And I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "role_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "email": "test@merchantX.com",
        "password": "testPassword"
    }
    """
    Then the response status code should be 201
    And merchant user with merchant id 1 and user id "test-auth-id" should be created

  Scenario: duplicated user error
    Given I successfully create OAuth client with email "test@merchantX.com" and user id "test-auth-id"
    And I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "role_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "email": "test@merchantX.com",
        "password": "testPassword"
    }
    """
    Then the response status code should be 201
    And merchant user with merchant id 1 and user id "test-auth-id" should be created
    When I get a response from Authentication Service from endpoint "/users" with status code 409
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "role_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "email": "test@merchantX.com",
        "password": "testPassword"
    }
    """
    Then the response status code should be 403

  Scenario: validation error
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "email": "test@.com",
        "password": ""
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
       "errors":[
          {
             "source":"user_email",
             "title":"This value is not a valid email address.",
             "code":"request_validation_error"
          },
          {
             "source":"user_password",
             "title":"This value should not be blank.",
             "code":"request_validation_error"
          },
          {
             "source":"role_uuid",
             "title":"This value should not be blank.",
             "code":"request_validation_error"
          }
       ]
    }
    """

  Scenario: validation error first and last name not provided
    Given I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "email": "test@merchantX.com",
        "password": "testPassword",
        "role_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70"
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
       "errors":[
          {
             "source":"first_name",
             "title":"This value should not be blank.",
             "code":"request_validation_error"
          },
          {
             "source":"last_name",
             "title":"This value should not be blank.",
             "code":"request_validation_error"
          }
       ]
    }
    """

  Scenario: Successfully retrieve orders list using role-level permissions for a new merchant user
    Given a merchant user exists with permission VIEW_ORDERS
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/orders"
    Then the response status code should be 200
    And the JSON response should be:
    """
      {
        "total": 0,
        "items":[]
      }
    """
  Scenario: Successfully retrieve orders list using overridden user-level permissions for a new merchant user
    Given a merchant user exists with overridden permission VIEW_ORDERS
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/orders"
    Then the response status code should be 200
    And the JSON response should be:
    """
      {
        "total": 0,
        "items":[]
      }
    """

  Scenario: merchant does not exist
    When I send a POST request to "/private/merchant/123/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "role_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "email": "test@merchantX.com",
        "password": "testPassword"
    }
    """
    Then the response status code should be 404
    And the JSON response should be:
    """
        {"errors":[{"title":"Merchant doesn't exist","code":"resource_not_found"}]}
    """


  Scenario: role does not exist
    Given I successfully create OAuth client with email "test@merchantX.com" and user id "test-auth-id"
    When I send a POST request to "/private/merchant/1/user" with body:
    """
    {
        "first_name": "name",
        "last_name": "last",
        "role_uuid": "c1255928-0725-4d9d-93de-22494c2c6e2d",
        "email": "test@merchantX.com",
        "password": "testPassword"
    }
    """
    Then the response status code should be 404
    And the JSON response should be:
    """
        {"errors":[{"title":"Role doesn't exist","code":"resource_not_found"}]}
    """
