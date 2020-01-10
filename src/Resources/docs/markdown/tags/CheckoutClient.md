## Checkout Widget Integration

To integrate the Billie checkout widget, the following steps are required:

1. Include Billie checkout widget bundle into your checkout page
2. Provide config data _(with `session_id` from server-side integration)_
3. Provide order details and call mount method
4. Handle widget responses
5. Apply customized styling _[optional]_

This document will go over details of how to do each step and explain easiest course of action to get the widget working.

### 1. Include Billie Checkout Widget Bundle into your Checkout Page

On the checkout page, the following code snippet needs to be included:
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

URL specified in `bcwSrc` is going to specify if you are using Sandbox or Production environment:

Sandbox: https://static-paella-sandbox.billie.io/checkout/billie-checkout.js  
Production: https://static.billie.io/checkout/billie-checkout.js   

This code snippet will inject our checkout script into the head of the webshop. That will download our widget bundle code and enable function calls to interact with the widget.  

It is **highly recommended** to add this code snippet as high as possible in document `head` so that download of the widget bundle is finished as soon as possible in order to ensure smooth user experience.  

### 2. Provide Config Data

During initialization of the the widget, we expect to get `session_id`. This can be obtained by calling [Checkout Session Create](#operation/checkout_session_create) with Billie from your backend and exposing it to the frontend. The `session_id` will be unique per order.
```html
<script>
  const billie_config_data = {
    'session_id': ':YOUR_SESSION_ID',
    'merchant_name': ':YOUR_NAME'
  }
</script>
```

`session_id` is needed to approve the order and should be known before mounting the widget.

Ideally if you are using server side rendering, this piece of code can be inserted into the document `head` with prefilled values. Otherwise please ensure there is [XHR request](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest) as soon as possible to your backend to retrieve `session_id`.

### 3. Provide Order Details and Call Mount Method

Once the user reviews his checkout basket and is ready to pay, he can click on the **Pay with Billie** button. When that happens we are expecting the `mount` method to be called. Easiest integration would be to inline the call of the function directly on the button, for example:
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

`BillieCheckoutWidget` is a promise which can accept optional `then` and `catch` methods. They can be added if the webshop needs to do something with the outcome of the order authorization. 

`then` block will receive as parameter the object with the authorised order details. 

`catch` block will receive the error object with reasons why the order creation was rejected.


#### 3.a Mount Method Parameters

`BillieCheckoutWidget.mount` expects two parameters:
- `billie_config_data` is object you have from step two which includes the `session_id` and `merchant_name`
- `billie_order_data` is collection of order data from user


#### 3.b Order Data Object

Order data object needs to contain all information about order in following format, required values are bolded:

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

Below you can find an example of orderData object in javascript

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

When submitted, the checkout widget will evaluate if an order with the given data can be accepted or not. If an order could not be accepted the widget will return the state `declined` with a specified decline_reason.

#### Response
After submitting the widget, following data will be returned to either `then` or `catch` block:
- `state` **[string]** `<= 255 characters`   
&nbsp;&nbsp;&nbsp;&nbsp;Enum: `authorized` `declined`
- `decline_reason` **[string]** `<= 255 characters`   
&nbsp;&nbsp;&nbsp;&nbsp;Enum: `risk_policy` `debtor_not_identified` `debtor_address` `debtor_limit_exceeded`
- `debtor_company` **[object]**  
  - `name` **[string]** `<= 255 characters`
  - `address_street` **[string]** `<= 255 characters`
  - `address_house_number` **[string]** `<= 255 characters`
  - `address_city` **[string]** `<= 255 characters`
  - `address_postal_code` **[string]** `<= 5 characters`
  - `address_country` **[string]** `2 characters` `^[A-Za-z]{2}$`
  
`decline_reason` will be set only when `state` value is `declined`

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
