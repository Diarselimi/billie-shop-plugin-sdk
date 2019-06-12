This authentication mechanism is based on the OAuth 2.0 prototocol and requires a Client ID, a Client Secret Key and
in some cases also a user and password.

With these credentials, the client should request a token using the `POST /api/v1/oauth/token` endpoint,
which should be used in the `Authorization` header together with the `Bearer` challenge to authenticate all the requests.

> Example: `Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c`.
