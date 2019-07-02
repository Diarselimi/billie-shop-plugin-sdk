For authentication and authorisation the API requires some requests to include valid API credentials via HTTP headers.
Different keys are used for the `sandbox` and `production` environments, which are completely independent
meaning that they cannot access or alter data from the other.

To obtain your credentials for both environments please contact us or send an email to: pad@billie.io.

## OAuth 2.0
The API uses OAuth 2.0 for authentication.

This authentication mechanism is based on the OAuth 2.0 prototocol and requires a Client ID, a Client Secret Key and
in some flows also a user (email) and password.

With these credentials, the client should request a token using the [oauth/token](#operation/oauth_token_create) endpoint,
which should be used in the `Authorization` header together with the `Bearer` challenge to authenticate all the requests.

> Example: `Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c`.
