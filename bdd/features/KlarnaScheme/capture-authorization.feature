Feature:
  Purpose - A capture signals commitment from the merchant to deliver goods equal to the capture amount. This is the point where the Payment Method creates debt for the customer and initiates dunning.
  Preconditions - A confirmed Authorization with a sufficient captured amount, created by the Acquirer making this request.
  Semantic validation - The sum of all refunds can exceed the sum of all captures by a margin that is configured per payment method, but will often be zero. The amount that exceeds captures is debited the Acquirer by the Payment Method so there is no risk involved for the Payment Method.
  A refund may optionally be associated with a capture_id. In that case, validation will simply be that the capture exists. The amount does not have to match that of the refund.
  Scheme operations - Internal accounting will increase the captured amount in the Authorization. The capture should always be accepted by the Payment Method.

  Background:
    When I add header "Authorization" with "Basic a2xhcm5hJTQwYmlsbGllLmlvOmtsYXJuYTEyMzQ="
    And GraphQL will respond to getMerchantDebtorDetails query


  Scenario: Attempt to capture a non existing authorization
    When I request "POST /authorizations/non-existing-uuid/captures"
    Then the response is 200 with body:
    """
    {
      "error_messages": [ "Request data are missing" ]
    }
    """

  Scenario: Capturing a part of an authorization
    Given I have orders with the following data
      | uuid                 | external_id | state   | gross | net | tax | duration | payment_uuid                         | workflow_name |
      | confirmed-order-uuid | C3PO        | created | 1000  | 900 | 100 | 30       | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 | order_v2      |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from volt service good response
    When I request "POST /authorizations/confirmed-order-uuid/captures" with body:
    """
{
    "payment_method_reference": "00112233-4455-6677-8899-aabbccddeeff",
    "amount": 10000,
    "shipping_info": [
        {
            "tracking_number": "tracking_number_1",
            "return_shipping_company": "Billie io",
            "return_tracking_url": "Tracking_2",
            "shipping_method": "Post",
            "shipping_company": "Billie gmbh",
            "return_tracking_number": "Tracking_3",
            "tracking_url": "google.com"
        }
    ],
    "description": "Ship the items",
    "capture_id": "Invoice_code",
    "tax_amount": 100,
    "order_lines": [
        {
            "product_identifiers": {
                "manufacturer_part_number": "6789",
                "brand": "Apple",
                "category_path": "technology",
                "global_trade_item_number": "12345"
            },
            "total_tax_amount": 9000,
            "name": "Apple m1",
            "quantity_unit": "5",
            "tax_rate": 900,
            "unit_price": 4500,
            "product_url": "apple.com",
            "total_discount_amount": 0,
            "image_url": "google.com/images",
            "total_amount": 225000,
            "type": "laptop",
            "quantity": 5,
            "reference": "no_reference"
        }
    ],
    "captured_at": "2021-06-06T01:00:00Z",
    "shipping_delay": 0
}
    """
    Then the response is 200 with empty body
    And the order C3PO is in state partially_shipped
    And queue should contain message with routing key invoice.create_invoice with below data:
    """
    {
      "uuid":"@string@",
      "externalCode":"Invoice_code",
      "orderExternalCode": "C3PO",
      "orderUuid": "confirmed-order-uuid",
      "customerUuid":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
      "debtorCompanyUuid":"c7be46c0-e049-4312-b274-258ec5aeeb70",
      "debtorSepaMandateUuid": "@string@",
      "debtorCompanyName":"Test User Company",
      "paymentDebtorUuid":"test",
      "grossAmount":"10000",
      "netAmount":"10000",
      "feeRate":2000,
      "grossFeeAmount":"23800",
      "netFeeAmount":"20000",
      "taxFeeAmount":"3800",
      "duration":30,
      "billingDate":"@string@",
      "services":["financing","dci"],
      "paymentUuid":"@string@"
    }
    """

  Scenario: Capture the full amount without tax money.
    Given I have orders with the following data
      | uuid                 | external_id | state   | gross | net | tax | duration | payment_uuid                         | workflow_name |
      | confirmed-order-uuid | C3PO        | created | 1000  | 1000| 0   | 30       | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 | order_v2      |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from volt service good response
    When I request "POST /authorizations/confirmed-order-uuid/captures" with body:
    """
{
    "amount": 100000,
    "capture_id": "some_external_id",
    "captured_at": "2021-06-06T01:00:00Z"
}
    """
    Then the response is 200 with empty body
    And the order C3PO is in state shipped

  Scenario: Trying to capture more than the authorization amount
    Given I have orders with the following data
      | uuid                 | external_id | state   | gross | net | tax | duration | payment_uuid                         | workflow_name |
      | confirmed-order-uuid | C3PO        | created | 1000  | 900 | 100 | 30       | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 | order_v2      |
    And GraphQL will respond to getMerchantDebtorDetails query
    When I request "POST /authorizations/confirmed-order-uuid/captures" with body:
    """
{
    "amount": 100001,
    "capture_id": "some_external_id",
    "captured_at": "2021-06-06T01:00:00Z"
}
    """
    Then the response is 200 with body:
    """
    {
      "error_messages": [ "Capture is not possible" ]
    }
    """
