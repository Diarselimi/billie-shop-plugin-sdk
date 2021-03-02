Feature:
  Merchant request debtor information change

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token

  Scenario: Successfully request debtor information change
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And I get from companies service get debtor response
    And I have default limits and no order created yet
    When I send a POST request to "/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/information-change-request" with body:
    """
    {
        "name": "Billie1",
        "address_city": "BilCity",
        "address_postal_code": "10887",
        "address_street": "Billiestr.",
        "address_house": "222",
        "tc_accepted": true
    }
    """
    Then the response status code should be 201

  Scenario: Failed to request debtor information change due to invalid request body
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And I get from companies service get debtor response
    And I have default limits and no order created yet
    When I send a POST request to "/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/information-change-request" with body:
        """
        {
            "address_street": "Billiestr.",
            "address_house": "222",
            "tc_accepted": false
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
           "source":"name"
        },
        {
           "title":"This value should not be blank.",
           "code":"request_validation_error",
           "source":"city"
        },
        {
           "title":"This value should not be blank.",
           "code":"request_validation_error",
           "source":"postal_code"
        },
        {
           "title":"This value should be true.",
           "code":"request_validation_error",
           "source":"tc_accepted"
        }
      ]
    }
    """

  Scenario: Successful automatic approve of debtor information change request
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from companies service update debtor positive response
    When I send a POST request to "/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/information-change-request" with body:
    """
    {
        "name": "Test User Company",
        "address_city": "BilCity",
        "address_postal_code": "10887",
        "address_street": "Billiestr.",
        "address_house": "222",
        "tc_accepted": true
    }
    """
    Then the response status code should be 201
    And I add "X-Api-Key" header equal to test
    And I add "X-Test" header equal to 1
    And I get from companies service get debtor response
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service get debtor response
    And the merchant debtor with companyUuid "c7be46c0-e049-4312-b274-258ec5aeeb70" should have all change requests seen

  Scenario: Notifications count for not seen change requests which transitioned to complete or declined
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And the following debtor information change requests exist:
      | uuid | company_uuid | is_seen | state                |
      | aaa  | aaa-bbb-ccc  | 0       | complete             |
      | bbb  | bbb-ccc-ddd  | 0       | declined             |
      | ccc  | ccc-ddd-eee  | 0       | confirmation_pending |
      | ddd  | ddd-eee-fff  | 1       | complete             |
    When I send a GET request to "/notifications"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "debtor_information_change_requests":2
    }
    """

  Scenario: Existing request is canceled when new one is created for debtor
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from companies service update debtor positive response
    And the following debtor information change requests exist:
      | uuid | company_uuid                         | is_seen | state                |
      | aaa  | c7be46c0-e049-4312-b274-258ec5aeeb70 | 0       | confirmation_pending |
    When I send a POST request to "/debtor/ad74bbc4-509e-47d5-9b50-a0320ce3d715/information-change-request" with body:
    """
    {
      "name": "Test User Company",
      "address_city": "BilCity",
      "address_postal_code": "10887",
      "address_street": "Billiestr.",
      "address_house": "222",
      "tc_accepted": true
    }
    """
    Then change request number 1 from debtor "ad74bbc4-509e-47d5-9b50-a0320ce3d715" should have state canceled
    Then change request number 1 from debtor "ad74bbc4-509e-47d5-9b50-a0320ce3d715" should have is_seen 1
    # The second change request will get state complete because it's auto approved
    And change request number 2 from debtor "ad74bbc4-509e-47d5-9b50-a0320ce3d715" should have state complete

  Scenario: Change request approved decision issued
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And the following debtor information change requests exist:
      | uuid | company_uuid                         | is_seen | state                |
      | aaa  | c7be46c0-e049-4312-b274-258ec5aeeb70 | 0       | confirmation_pending |
    And I get from companies service update debtor positive response
    When I consume an existing queue message of type company_information_change_request.company_information_change_request_decision_issued containing this payload:
    """
    {
      "request_uuid":"aaa",
      "decision":"approved"
    }
    """
    Then debtor information change request aaa should have state complete

  Scenario: Change request declined decision issued
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And the following debtor information change requests exist:
      | uuid | company_uuid | is_seen | state                |
      | aaa  | aaa-bbb-ccc  | 0       | confirmation_pending |
    When I consume an existing queue message of type company_information_change_request.company_information_change_request_decision_issued containing this payload:
    """
      {
        "request_uuid":"aaa",
        "decision":"declined"
      }
    """
    Then debtor information change request aaa should have state declined

  Scenario: Invalidate debtor external data when change request decision issued approved
    Given a merchant user exists with role "admin" and permission CHANGE_DEBTOR_INFORMATION
    And the following debtor information change requests exist:
      | uuid | company_uuid                         | is_seen | state                |
      | aaa  | c7be46c0-e049-4312-b274-258ec5aeeb70 | 0       | confirmation_pending |
    And the following debtor external data exist:
      | id | name        | legal_form | address_id | billing_address_id | merchant_external_id | debtor_data_hash |
      | 1  | Random Name | GmbH       | 1          | 1                  | external-id-123      | random-hash-123  |
    When I consume an existing queue message of type company_information_change_request.company_information_change_request_decision_issued containing this payload:
    """
    {
      "request_uuid":"aaa",
      "decision":"approved"
    }
    """
    Then debtor information change request aaa should have state complete
    And debtor external data "1" should have been invalidated "ext_id"
