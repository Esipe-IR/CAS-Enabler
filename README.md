CAS Enabler
===========

CAS Enabler is a fast and convenient way for people to log into your app across multiple platforms with their UPEM account.

API IS IN PROGRESS ... Breaking changes may follow

## Use

    - Register a Service at this URL :
    - Install Client SDK
    - Enjoy

## Client SDK

## API

### Auth
`/auth`

Force authentication.

Content-Type: HTML

### Generate token
`/api/token`

Generate a Json Web Token with user information in it if :

    - User is logged in
    - Service exist
    - Service is allowed by user

### Verify token
`/api/token/{token}`

Decrypt a Json Web Token and return plain decoded object

