Feature: Retrieve and search all orders of a merchant

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1
	And a merchant user exists
	And I get from Oauth service a valid user token
	And I add "Authorization" header equal to test

  Scenario: Successfully retrieve orders
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And I get from companies service get debtor response
	And I get from payments service get debtor response
	When I send a GET request to "/public/orders"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "external_code":"XF43Y",
			 "uuid":"test123",
			 "state":"new",
			 "reasons":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"house_number":"10",
				"street":"Heinrich-Heine-Platz",
				"postal_code":"10179",
				"city":"Berlin",
				"country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
				"number":null,
				"payout_amount":null,
				"outstanding_amount":null,
				"fee_amount":null,
				"fee_rate":null,
				"due_date":null
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
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
			 }
		  }
		]
	  }
	"""

  Scenario: Search orders by external code
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
  	And I get from companies service get debtor response
	And I get from payments service get debtor response
	When I send a GET request to "/orders?search=XF43Y"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "external_code":"XF43Y",
			 "uuid":"test123",
			 "state":"new",
			 "reasons":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"house_number":"10",
				"street":"Heinrich-Heine-Platz",
				"postal_code":"10179",
				"city":"Berlin",
				"country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
				"number":null,
				"payout_amount":null,
				"outstanding_amount":null,
				"fee_amount":null,
				"fee_rate":null,
				"due_date":null
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
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
			 }
		  }
		]
	  }
	"""

  Scenario: Search orders by uuid
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
  	And I get from companies service get debtor response
	And I get from payments service get debtor response
	When I send a GET request to "/orders?search=test123"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "external_code":"XF43Y",
			 "uuid":"test123",
			 "state":"new",
			 "reasons":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"house_number":"10",
				"street":"Heinrich-Heine-Platz",
				"postal_code":"10179",
				"city":"Berlin",
				"country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
				"number":null,
				"payout_amount":null,
				"outstanding_amount":null,
				"fee_amount":null,
				"fee_rate":null,
				"due_date":null
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
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
			 }
		  }
		]
	  }
	"""

  Scenario: Search orders - no results
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
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
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
  	And I get from companies service get debtor response
	And I get from payments service get debtor response
	When I send a GET request to "/orders?filters[merchant_debtor_id]=ad74bbc4-509e-47d5-9b50-a0320ce3d715"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
		"total": 1,
		"items":[
		  {
			 "external_code":"XF43Y",
			 "uuid":"test123",
			 "state":"new",
			 "reasons":null,
			 "amount":1000,
			 "amount_net": 900.00,
      		 "amount_tax": 100.00,
			 "created_at":"2019-05-07",
			 "debtor_company":{
				"name":"Test User Company",
				"house_number":"10",
				"street":"Heinrich-Heine-Platz",
				"postal_code":"10179",
				"city":"Berlin",
				"country":"DE"
			 },
			 "bank_account":{
				"iban":"DE1234",
				"bic":"BICISHERE"
			 },
			 "invoice":{
				"number":null,
				"payout_amount":null,
				"outstanding_amount":null,
				"fee_amount":null,
				"fee_rate":null,
				"due_date":null
			 },
			 "debtor_external_data":{
				"name":"test",
				"address_country":"TE",
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
			 }
		  }
		]
	  }
	"""

  Scenario: Filter by merchant debtor UUID - no results
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	When I send a GET request to "/orders?filters[merchant_debtor_id]=ad74bbc4-449e-47d5-9b50-a0320ce3d715"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
		"total": 0,
		"items":[]
	  }
	"""
