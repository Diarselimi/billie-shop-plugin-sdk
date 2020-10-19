To integrate the Billie checkout widget, the following steps are required:

1. Add the Billie checkout widget bundle to your checkout page
2. Provide config data _(with the `session_id` from the server-side integration)_
3. Provide order details and call the mount method
4. Handle the widget responses
5. Apply customized styling _[optional]_

This section provides a step-by-step guide of the setup process.

### 1. Include Billie Checkout Widget Bundle into your Checkout Page

On the checkout page, the following code snippet must be included:
```html
<script>
  var bcwSrc = 'https://static-paella-sandbox.billie.io/checkout/billie-checkout.js';
  (function(w,d,s,o,f,js,fjs){
    w['BillieCheckoutWidget']=o;w[o]=w[o]||function(){(w[o].q=w[o].q||[]).push(arguments)};
    w.billieSrc=f;js=d.createElement(s);fjs=d.getElementsByTagName(s)[0];js.id=o;
    js.src=f;js.charset='utf-8';js.async=1;fjs.parentNode.insertBefore(js,fjs);bcw('init');
  }(window,document,'script','bcw', bcwSrc));
</script>
```

The URL contained in `bcwSrc` specifies whether you are using the Sandbox or Production environment:

Sandbox: https://static-paella-sandbox.billie.io/checkout/billie-checkout.js

Production: https://static.billie.io/checkout/billie-checkout.js

The code snippet above injects our checkout script into the `head` of the webshop. This will download our widget bundle code and enable function calls to interact with the widget.

It is **strongly recommended** to add this code snippet as high as possible in the document head to ensure that the widget download completes as soon as possible, providing a smooth user experience.

### 2. Provide Config Data

During initialization of the widget, the `session_id` is required. This can be obtained by calling [Checkout Session Create](#operation/checkout_session_create) from your backend and exposing it to the frontend. The `session_id` will be unique per order.
```html
<script>
  const billie_config_data = {
    'session_id': ':YOUR_SESSION_ID',
    'merchant_name': ':YOUR_NAME'
  }
</script>
```
The `session_id` is needed to approve the order and should be known before mounting the widget.

Ideally, if you are using server side rendering, this piece of code should be inserted into the document head with prefilled values. Otherwise please ensure there is an [XHR request](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest) as early as possible to your backend to retrieve the `session_id`.

### 3. Provide Order Details and Call Mount Method

Once the user reviews his checkout basket and is ready to pay, he can click on the **Pay with Billie** button. When this happens, Billie expects the `mount` method to be called. The easiest integration option is to inline the function call directly on the button, for example:
```html
<script>
  function payWithBillie() {
    // prepare / get all the data and assign it to billie_order_data, please refer to 3a section
    BillieCheckoutWidget.mount({
      billie_config_data: billie_config_data, 
      billie_order_data: billie_order_data
    })
    .then(function success(ao) {
      // code to execute when approved order
      // console.log('Approved order', ao);
    })
    .catch(function failure(err) {
      // code to execute when there is an error or when order is rejected
      // console.log('Error occurred', err);
    });
  }
</script>
<button onclick="payWithBillie()"> Pay with Billie </button>
```

`BillieCheckoutWidget` is a JavaScript promise which can accept optional `then` and `catch` methods. They can be added if the webshop needs to perform an action based on the outcome of the order authorization. 

The `then` block will receive as parameter the object with the authorised order details.

The `catch` block will receive the error object with reasons why the order creation was rejected.


#### 3.a Mount Method Parameters

`BillieCheckoutWidget.mount` expects two parameters:
- `billie_config_data` is the object received in step two which includes the `session_id` and `merchant_name`
- `billie_order_data`  is a collection of order data from the user


#### 3.b Order Data Object

The order data object must contain all information about an order, in the following format (required values are **bolded**):

- `amount` **[object]**
   - `gross` **[float]** `> 0`
   - `tax` **[float]** `>= 0`
   - `net` **[float]** `> 0`
- `duration` **[integer]** `[7...120]`
- `delivery_address` **[object]**
  - `street` **[string]** `<= 255 characters`
  - `house_number`  _[string]_ `<= 255 characters`
  - `addition` _[string]_ `<= 255 characters`
  - `city` **[string]** `<= 255 characters`
  - `postal_code` **[string]** `<= 5 characters`
  - `country` **[string]** `2 characters` `^[A-Za-z]{2}$`
- `debtor_company` **[object]**
  - `name` **[string]** `<= 255 characters`
  - `established_customer` _[boolean]_ 
  - `address_street` **[string]** `<= 255 characters`
  - `address_house_number` **[string]** `<= 255 characters`
  - `address_addition` _[string]_  `<= 255 characters`
  - `address_city` **[string]** `<= 255 characters`
  - `address_postal_code` **[string]** `<= 5 characters`
  - `address_country` **[string]** `2 characters` `^[A-Za-z]{2}$`
- `debtor_person`  **[object]**
  - `salutation` **[string]** `1 character` `["m" / "f"]`
  - `first_name` _[string]_ `<= 255 characters`
  - `last_name` _[string]_ `<= 255 characters`
  - `phone_number` _[string]_ `>= 5 characters`, `<= 20 characters` `^(\+|\d|\()[ \-\/0-9()]{5,20}$`
  - `email` **[string]** `<= 255 characters` `valid email`
- `line_items` **[array of objects]**
  - `external_id` **[string]** `<= 255 characters`
  - `title` **[string]** `<= 255 characters`
  - `description` _[string]_ `<= 255 characters`
  - `quantity` **[string]** `>= 1`
  - `category` _[string]_ `<= 255 characters`
  - `brand` _[string]_ `<= 255 characters`
  - `gtin` _[string]_ `<= 255 characters`
  - `mpn` _[string]_ `<= 255 characters`
  - `amount` **[object]**
    - `gross` **[float]** `> 0`
    - `tax` **[float]** `>= 0`
    - `net` **[float]** `> 0`

Below you can find an example of `orderData` object in javascript
```javascript
const billie_order_data = {
  "amount": { "net": 100, "gross": 100, "tax": 0 },
  "comment": "string",
  "duration": 30,
  "delivery_address": {
    "house_number": "string",
    "street": "string",
    "city": "string",
    "postal_code": "10000",
    "country": "DE",
    "addition": "string"
  },
  "debtor_company": {
    "name": "string",
    "established_customer": false,
    "address_house_number": "string",
    "address_street": "string",
    "address_city": "string",
    "address_postal_code": "10000",
    "address_country": "DE",
    "address_addition": "string"
  },
  "debtor_person": {
    "salutation": "m",
    "first_name": "string",
    "last_name": "string",
    "phone_number": "030 31199251",
    "email": "james.smith@example.com"
  },
  "line_items": [
    {
      "external_id": "string",
      "title": "string",
      "description": "string",
      "quantity": 1,
      "category": "string",
      "brand": "string",
      "gtin": "string",
      "mpn": "string",
      "amount": { "net": 100, "gross": 100, "tax": 0 },
    }
    // , ...
  ]
};
```

### 4. Handle Widget Responses

When submitted, the checkout widget will evaluate if an order with the given data can be accepted. 
- If an order cannot be accepted, the widget will return the state `declined` with a specified `decline_reason`
- If an order can be accepted immediately the widget will return the state `authorized`. 
- It is also possible that some additional checks need to be conducted by Billie in order to accept the order. In this case the widget will return the state `pre_waiting`. 

In both `authorized` and `pre_waiting` states the `checkout-session-confirm` call must be used to confirm the order creation. 


#### Response
After submitting the widget, following data will be returned to either `then` or `catch` block:
- `state` **[string]** `<= 255 characters`   
&nbsp;&nbsp;&nbsp;&nbsp;Enum: `authorized` `declined` `pre_waiting`
- `decline_reason` **[string]** `<= 255 characters`   
&nbsp;&nbsp;&nbsp;&nbsp;Enum: `validation_error` `risk_policy` `debtor_not_identified` `debtor_address` `risk_scoring_failed` `debtor_limit_exceeded` `unknown_error`
- `validation_error_source` **[string]**  `<= 255 characters`
- `debtor_company` **[object]**  
  - `name` **[string]** `<= 255 characters`
  - `address_street` **[string]** `<= 255 characters`
  - `address_house_number` **[string]** `<= 255 characters`
  - `address_city` **[string]** `<= 255 characters`
  - `address_postal_code` **[string]** `<= 5 characters`
  - `address_country` **[string]** `2 characters` `^[A-Za-z]{2}$`

_**Special note**_  
* `decline_reason` will be set only when `state` value is `declined`
* `validation_error_source` will be set only when `decline_reason` value is `validation_error`. We will fill this property with information to help you find which fields are failing validation checks. 

#### 5. Apply Customized Styling

It is possible to set some CSS rules to be applied over widget in a simple format. Simply use this snippet:

```html
<style>
  .billie-checkout-modal {
    --c-primary: #FF4338;
  }
</style>
```

Other available options are:
- `c-primary` -  color of submit button
- `s-font` - font size inside the widget 
- `s-font-small` - font size of small text inside the widget 
