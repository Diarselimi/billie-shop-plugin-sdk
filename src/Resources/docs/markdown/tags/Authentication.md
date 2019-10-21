This API uses OAuth 2.0 for secure authentication. 

Different keys are used for the `sandbox` and `production` environments. Both systems operate completely independent, however both show largely 
the same response behaviour. The major difference is that sandbox does not trigger any payment events (payout to merchant, repayment 
by debtors, etc.). To obtain your credentials for both environments please contact Billie's merchant integration support 
via telephone ( [+49-30-120872485](tel:+4930120872485) ) or email ( pad@billie.io ).

This authentication mechanism is based on the OAuth 2.0 protocol and requires a Client ID and a Client Secret Key (client credentials).

Before triggering any API call, a so called JSON web token (JWT) must be requested through the `oauth/token` endpoint described below
by using your client credentials.
In all following requests / API calls, this JWT token is used for authorization and must be passed as type "Bearer" token 
in the Authorization header. Example: `Authorization: Bearer eyJhbGciOiJIUzI...`

Renewal of the bearer token / JWT:

Every token expires automatically after 8 hours. After expiration of an existing token a new token must be requested through the 
`POST oauth/token` endpoint. Implementation best practice is to always try sending API calls first with the previously stored token. 
Sending API calls with an expired token will be answered with an `HTTP 401` (Unauthorized request or invalid credentials) response.

After receiving the `401` error you will simply need to request a new token by triggering again the `oauth/token` endpoint. 
After receiving the new valid token, resend the previously failed API call. This procedure allows for optimal API interaction, 
ensures reduced system load on both ends, and is easy to implement as you don't need to keep track of token expiration times.

There is another endpoint available for checking if a token is still valid or not: `GET oauth/token`.
