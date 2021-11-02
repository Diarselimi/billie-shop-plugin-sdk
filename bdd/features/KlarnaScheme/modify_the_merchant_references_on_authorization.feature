Feature: Modify the merchant references on authorization.
  A confirmed Authorization is referred to by the payment_method_reference.
  The Payment Method will be contacted with the updated merchant references.
  No internal information will be updated.
  This notification should always be accepted by the Payment Method.

  Scenario: When merchant-references is called we get the info and save them into order external id
    Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
    When I request "POST /authorizations/test-order-uuid/merchant-references"
      """
      {
        "merchant_reference1": "TEST123",
        "merchant_reference2": "SECOND_REFERENCE",
        "payment_method_reference": "00112233-4455-6677-8899-aabbccddeeff"
      }
      """
    Then the response is 200 with empty body

  Scenario: When merchant-references is called with null merchant_reference1
    When I request "POST /authorizations/test-order-uuid/merchant-references"
      """
      {
        "merchant_reference1": null,
        "merchant_reference2": "SECOND_REFERENCE",
        "payment_method_reference": "00112233-4455-6677-8899-aabbccddeeff"
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "merchant_reference1 is required" ]
      }
      """

