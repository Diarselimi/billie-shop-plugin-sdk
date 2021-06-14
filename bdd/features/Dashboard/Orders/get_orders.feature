Feature: Retrieve and search all orders of a merchant

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ORDERS
    And I get from payments service get orders details response
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Successfully retrieve orders that are not in state new
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/public/orders"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 0,
      "items": []
    }
    """

  Scenario: Successfully retrieve orders
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF123       | created | 900   | 800 | 100 | 30       | "test comment" | 123456a      |
      | XF125       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456b      |
    And I get from invoice-butler service good response no CreditNotes
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a GET request to "/public/orders?sort_by=id,desc"
    Then print last response
    Then the response status code should be 200
    Then the JSON response should be:
    """
    {
      "total": 2,
      "items": [
        {
          "uuid": "test-order-uuidXF125",
          "order_id": "XF125",
          "created_at": "2019-05-20 13:00:00",
          "state": "created",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 1000,
          "invoice": {
            "uuid": null,
            "invoice_number": null,
            "created_at": null,
            "due_date": "2019-06-19",
            "amount": null,
            "outstanding_amount": null
          },
          "invoices": [],
          "workflow_name": "order_v1"
        },
        {
          "uuid": "test-order-uuidXF123",
          "order_id": "XF123",
          "created_at": "2019-05-20 13:00:00",
          "state": "created",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 900,
          "invoice": {
            "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
            "invoice_number": "some_code",
            "created_at": "2020-10-12 12:12:12",
            "due_date": "2019-06-19",
            "amount": 123.33,
            "outstanding_amount": 500
          },
          "invoices": [
            {
              "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
              "invoice_number": "some_code",
              "created_at": "2020-10-12 12:12:12",
              "due_date": "2020-12-26",
              "amount": 123.33,
              "outstanding_amount": 500
            }
          ],
          "workflow_name": "order_v1"
        }
      ]
    }
    """

  Scenario: Search orders by external code
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    When I send a GET request to "/orders?search=XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 1,
      "items": [
        {
          "uuid": "test-order-uuidXF43Y",
          "order_id": "XF43Y",
          "created_at": "2019-05-20 13:00:00",
          "state": "created",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 1000,
          "invoice": {
            "uuid": null,
            "invoice_number": null,
            "created_at": null,
            "due_date": "2019-06-19",
            "amount": null,
            "outstanding_amount": null
          },
          "invoices": [],
          "workflow_name": "order_v1"
        }
      ]
    }
    """

  Scenario: Search orders filtering by state using deepObject param serialization without numeric keys
    Given I have orders with the following data
      | external_id | state      | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | authorized | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    When I send a GET request to "/orders?filters[state][]=authorized&filters[state][]=created"
    Then the response status code should be 200
    And the JSON response should be:
	  """
    {
      "total": 1,
      "items": [
        {
          "uuid": "test-order-uuidXF43Y",
          "order_id": "XF43Y",
          "created_at": "2019-05-20 13:00:00",
          "state": "authorized",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 1000,
          "invoice": {
            "uuid": null,
            "invoice_number": null,
            "created_at": null,
            "due_date": "2019-06-19",
            "amount": null,
            "outstanding_amount": null
          },
          "invoices": [],
          "workflow_name": "order_v1"
        }
      ]
    }
	  """

  Scenario: Search orders filtering by state gives no results
    Given I have a created order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/orders?filters[state][0]=shipped"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 0,
      "items": []
    }
    """

  Scenario: Search orders by uuid
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    When I send a GET request to "/orders?search=test-order-uuid"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 1,
      "items": [
        {
          "uuid": "test-order-uuidXF43Y",
          "order_id": "XF43Y",
          "created_at": "2019-05-20 13:00:00",
          "state": "created",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 1000,
          "invoice": {
            "uuid": null,
            "invoice_number": null,
            "created_at": null,
            "due_date": "2019-06-19",
            "amount": null,
            "outstanding_amount": null
          },
          "invoices": [],
          "workflow_name": "order_v1"
        }
      ]
    }
    """

  Scenario: Invalid request
    When I send a GET request to "/orders?limit=-1&sort_by=test,foo"
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "source": "sort_by",
          "title": "The value you selected is not a valid choice.",
          "code": "request_validation_error"
        },
        {
          "source": "sort_direction",
          "title": "The value you selected is not a valid choice.",
          "code": "request_validation_error"
        },
        {
          "source": "limit",
          "title": "This value should be greater than 0.",
          "code": "request_validation_error"
        }
      ]
    }
    """

  Scenario: Filter by merchant debtor UUID
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    When I send a GET request to "/orders?filters[merchant_debtor_id]=ad74bbc4-509e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 1,
      "items": [
        {
          "uuid": "test-order-uuidXF43Y",
          "order_id": "XF43Y",
          "created_at": "2019-05-20 13:00:00",
          "state": "created",
          "duration": 30,
          "due_date": "2019-06-19",
          "amount": 1000,
          "invoice": {
            "uuid": null,
            "invoice_number": null,
            "created_at": null,
            "due_date": "2019-06-19",
            "amount": null,
            "outstanding_amount": null
          },
          "invoices": [],
          "workflow_name": "order_v1"
        }
      ]
    }
    """

  Scenario: Filter by merchant debtor UUID - no results
    Given I have a created order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/orders?filters[merchant_debtor_id]=ad74bbc4-449e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 0,
      "items": []
    }
    """
