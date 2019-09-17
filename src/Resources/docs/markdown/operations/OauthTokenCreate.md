<!-- Grants documentation taken from: https://oauth2.thephpleague.com/ -->

### Flow

The client sends a POST request with following body parameters to the authorization server:

* `grant_type` with the value `client_credentials`
* `client_id` with the client's ID
* `client_secret` with the client's secret

The authorization server will respond with a JSON object containing the following properties:

* `token_type` with the value `Bearer`
* `expires_in` with an integer representing the TTL (in seconds) of the access token (Default is 8 hours)
* `access_token` a JWT signed with the authorization server's private key

The `access_token` should be used in the `Authorization` header together with the `Bearer` challenge to authenticate all the requests.

> Example: `Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c`.
