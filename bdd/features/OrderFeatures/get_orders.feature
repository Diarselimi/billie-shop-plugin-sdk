Feature: Retrieve and search all orders of a merchant

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
	  And I add "Authorization" header equal to "Bearer someToken"
	  And I get from Oauth service a valid user token
	  And a merchant user exists with permission VIEW_ORDERS
    And I get from payments service get orders details response

  Scenario: Successfully retrieve orders that are not in state new
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/public/orders"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 0,
		"items":[]
	  }
	"""

  Scenario: Successfully retrieve orders
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF123       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
      | XF125       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456b      |
    And I get from companies service get debtors response
    And I get from payments service get orders details response
    And I get from payments service get order details response
    And I get from payments service get debtor response
    When I send a GET request to "/public/orders"
    Then the response status code should be 200
    Then the JSON response should be:
    """
    {
        "total": 2,
        "items": [
            {
                "reasons": null,
                "order_id": "XF123",
                "debtor_external_data": {
                    "merchant_customer_id": "ext_id",
                    "name": "test",
                    "industry_sector": "test",
                    "address_postal_code": "test",
                    "address_country": "TE",
                    "address_city": "testCity",
                    "address_street": "test",
                    "address_house": "test"
                },
                "decline_reason": null,
                "dunning_status": null,
                "uuid": "test-order-uuidXF123",
                "debtor_company": {
                    "address_house_number": "10",
                    "name": "Test User Company",
                    "address_postal_code": "10179",
                    "address_country": "DE",
                    "address_street": "Heinrich-Heine-Platz",
                    "address_city": "Berlin"
                },
                "invoice": {
                    "invoice_number": null,
                    "payout_amount": 1000,
                    "outstanding_amount":1000,
                    "fee_amount": 10,
                    "fee_rate": 1,
                    "due_date": "1978-11-20",
                    "pending_merchant_payment_amount": 0,
                    "pending_cancellation_amount": 0
                },
                "bank_account": {
                    "bic": "BICISHERE",
                    "iban": "DE1234"
                },
                "state": "created",
                "duration": 30,
                "created_at": "2019-05-20T13:00:00+0200",
                "shipped_at": null,
                "billing_address": {
                    "city": "test",
                    "country": "TE",
                    "house_number": "test",
                    "street": "test",
                    "postal_code": "test"
                },
                "amount_net": 900,
                "delivery_address": {
                    "city": "test",
                    "country": "TE",
                    "house_number": "test",
                    "street": "test",
                    "postal_code": "test"
                },
                "amount_tax": 100,
                "amount": 1000
            },
            {
                "reasons": null,
                "order_id": "XF125",
                "debtor_external_data": {
                    "merchant_customer_id": "ext_id",
                    "name": "test",
                    "industry_sector": "test",
                    "address_postal_code": "test",
                    "address_country": "TE",
                    "address_city": "testCity",
                    "address_street": "test",
                    "address_house": "test"
                },
                "decline_reason": null,
                "dunning_status": null,
                "uuid": "test-order-uuidXF125",
                "debtor_company": {
                    "address_house_number": "10",
                    "name": "Test User Company",
                    "address_postal_code": "10179",
                    "address_country": "DE",
                    "address_street": "Heinrich-Heine-Platz",
                    "address_city": "Berlin"
                },
                "invoice": {
                    "invoice_number": null,
                    "payout_amount": 1000,
                    "outstanding_amount":1000,
                    "fee_amount": 10,
                    "fee_rate": 1,
                    "due_date": "1978-11-20",
                    "pending_merchant_payment_amount": 0,
                    "pending_cancellation_amount": 0
                },
                "bank_account": {
                    "bic": "BICISHERE",
                    "iban": "DE1234"
                },
                "state": "created",
                "duration": 30,
                "created_at": "2019-05-20T13:00:00+0200",
                "shipped_at": null,
                "billing_address": {
                    "city": "test",
                    "country": "TE",
                    "house_number": "test",
                    "street": "test",
                    "postal_code": "test"
                },
                "amount_net": 900,
                "delivery_address": {
                    "city": "test",
                    "country": "TE",
                    "house_number": "test",
                    "street": "test",
                    "postal_code": "test"
                },
                "amount_tax": 100,
                "amount": 1000
            }
        ]
    }
    """

  Scenario: Search orders by external code
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?search=XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "order_id":"XF43Y",
			 "uuid":"test123",
			 "state":"created",
			 "reasons":null,
			 "decline_reason":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"address_house_number":"10",
				"address_street":"Heinrich-Heine-Platz",
				"address_postal_code":"10179",
				"address_city":"Berlin",
				"address_country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
        "invoice_number": null,
        "payout_amount": 1000,
        "outstanding_amount":1000,
        "fee_amount": 10,
        "fee_rate": 1,
        "due_date": "1978-11-20",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
				"address_city": "testCity",
				"address_postal_code":"test",
				"address_street":"test",
				"address_house":"test",
				"industry_sector":"test",
				"merchant_customer_id":"ext_id"
			 },
			 "duration":30,
			 "dunning_status": null,
			 "shipped_at":null,
			 "delivery_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
			 },
			 "billing_address":{
			    "house_number":"test",
			    "street":"test",
			    "city":"test",
			    "postal_code":"test",
			    "country":"TE"
             }
		  }
		]
	  }
	"""

  Scenario: Search orders filtering by state using deepObject param serialization with numeric keys
    Given I have orders with the following data
      | external_id | state      | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | authorized | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?filters[state][0]=authorized&filters[state][1]=created"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "order_id":"XF43Y",
			 "uuid":"test123",
			 "state":"authorized",
			 "reasons":null,
			 "decline_reason":null,
			 "amount":1000,
			 "amount_net": 900.00,
       "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"address_house_number":"10",
				"address_street":"Heinrich-Heine-Platz",
				"address_postal_code":"10179",
				"address_city":"Berlin",
				"address_country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
        "invoice_number": null,
        "payout_amount": 1000,
        "outstanding_amount":1000,
        "fee_amount": 10,
        "fee_rate": 1,
        "due_date": "1978-11-20",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
				"address_city": "testCity",
				"address_postal_code":"test",
				"address_street":"test",
				"address_house":"test",
				"industry_sector":"test",
				"merchant_customer_id":"ext_id"
			 },
			 "duration":30,
			 "dunning_status": null,
			 "shipped_at":null,
			 "delivery_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
			 },
			 "billing_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
             }
		  }
		]
	  }
	"""

  Scenario: Search orders filtering by state using deepObject param serialization without numeric keys
    Given I have orders with the following data
      | external_id | state      | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | authorized | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?filters[state][]=authorized&filters[state][]=created"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "order_id":"XF43Y",
			 "uuid":"test123",
			 "state":"authorized",
			 "reasons":null,
			 "decline_reason":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"address_house_number":"10",
				"address_street":"Heinrich-Heine-Platz",
				"address_postal_code":"10179",
				"address_city":"Berlin",
				"address_country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
			 	 "invoice_number": null,
			 	 "payout_amount": 1000,
			 	 "outstanding_amount":1000,
			 	 "fee_amount": 10,
			 	 "fee_rate": 1,
			 	 "due_date": "1978-11-20",
			 	 "pending_merchant_payment_amount": 0,
			 	 "pending_cancellation_amount": 0
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
				"address_city": "testCity",
				"address_postal_code":"test",
				"address_street":"test",
				"address_house":"test",
				"industry_sector":"test",
				"merchant_customer_id":"ext_id"
			 },
			 "duration":30,
			 "dunning_status": null,
			 "shipped_at":null,
			 "delivery_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
			 },
			 "billing_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
             }
		  }
		]
	  }
	"""

  Scenario: Search orders filtering by state gives no results
    Given I have a created order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?filters[state][0]=shipped"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 0,
		"items":[]
	  }
	"""

  Scenario: Search orders by uuid
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?search=test-order-uuid"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "order_id":"XF43Y",
			 "uuid":"test123",
			 "state":"created",
			 "reasons":null,
			 "decline_reason":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"address_house_number":"10",
				"address_street":"Heinrich-Heine-Platz",
				"address_postal_code":"10179",
				"address_city":"Berlin",
				"address_country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
        "invoice_number": null,
        "payout_amount": 1000,
        "outstanding_amount":1000,
        "fee_amount": 10,
        "fee_rate": 1,
        "due_date": "1978-11-20",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
				"address_city": "testCity",
				"address_postal_code":"test",
				"address_street":"test",
				"address_house":"test",
				"industry_sector":"test",
				"merchant_customer_id":"ext_id"
			 },
			 "duration":30,
			 "dunning_status": null,
			 "shipped_at":null,
			 "delivery_address":{
				"house_number":"test",
				"street":"test",
				"city": "test",
				"postal_code":"test",
				"country":"TE"
			 },
			 "billing_address":{
			    "house_number":"test",
			    "street":"test",
			    "city":"test",
			    "postal_code":"test",
			    "country":"TE"
             }
		  }
		]
	  }
	"""

  Scenario: Search orders - no results
    Given I have a created order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/orders?search=AnySearchString"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 0,
		"items":[]
	  }
	"""

  Scenario: Invalid request
    When I send a GET request to "/orders?limit=-1&sort_by=test,des"
    Then the response status code should be 400
    And the JSON response should be:
	"""
	  {
		 "errors":[
			{
			   "source":"sort_by",
			   "title":"The value you selected is not a valid choice.",
			   "code":"request_validation_error"
			},
			{
			   "source":"sort_direction",
			   "title":"The value you selected is not a valid choice.",
			   "code":"request_validation_error"
			},
			{
			   "source":"limit",
			   "title":"This value should be greater than 0.",
			   "code":"request_validation_error"
			}
		 ]
	  }
	"""

  Scenario: Filter by merchant debtor UUID
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | payment_uuid |
      | XF43Y       | created | 1000  | 900 | 100 | 30       | "test comment" | 123456a      |
    And I get from companies service get debtors response
    And I get from payments service get debtor response
    When I send a GET request to "/orders?filters[merchant_debtor_id]=ad74bbc4-509e-47d5-9b50-a0320ce3d715"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "order_id":"XF43Y",
			 "uuid":"test123",
			 "state":"created",
			 "reasons":null,
			 "decline_reason":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"address_house_number":"10",
				"address_street":"Heinrich-Heine-Platz",
				"address_postal_code":"10179",
				"address_city":"Berlin",
				"address_country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
        "invoice_number": null,
        "payout_amount": 1000,
        "outstanding_amount":1000,
        "fee_amount": 10,
        "fee_rate": 1,
        "due_date": "1978-11-20",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
				"address_city": "testCity",
				"address_postal_code":"test",
				"address_street":"test",
				"address_house":"test",
				"industry_sector":"test",
            	"merchant_customer_id":"ext_id"
			 },
			 "duration":30,
			 "dunning_status": null,
			 "shipped_at":null,
			 "delivery_address":{
				"house_number":"test",
				"street":"test",
				"city":"test",
				"postal_code":"test",
				"country":"TE"
			 },
			 "billing_address":{
			    "house_number":"test",
			    "street":"test",
			    "city":"test",
			    "postal_code":"test",
			    "country":"TE"
             }
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
		"items":[]
	  }
	"""
