CAS Enabler
===========

CAS Enabler  is a fast and convenient way for people to log into your app across multiple platforms with their UPEM account.

## Use

    - Register a Service at this URL :
    - Install Client SDK
    - Enjoy

## Prerequist

    - Registered service
    - Service allow by the user
    - Valid service URL & valid service return type (JSON)

## Client SDK

## API

### Allow Service
`/service/{uid}`

Where "{uid}" is the uid of your service.
Enable an user to allow your service

### Auth
`/auth`

Force authentication.

### Call Service
`/api/service/call/{uid}`

Where "{uid}" is the uid of your service. Call your service with user info if the user has enable your service.

You can add a parameter "callback" in order to enable JSONP strategy.

Ex:
/api/service/call/SOME-UID?callback=callback

Result:

    - Call your service with `user` parameter in JSON.
    - Put result of your service in `data` key

`
callback({
    "status": true,
    "code": 0,
    "data": {"MyService": "MyValue"},
    "error": null
});
`

