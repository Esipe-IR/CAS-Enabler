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

### Auth
`/auth`

Force authentication.

### Register Service
`/service/register`

Register a service.

Content-Type : HTML

### Allow Service
`/service/{uid}/allow`

Where "{uid}" is the uid of your service.
Enable an user to allow your service.

Content-Type: HTML

### Call Service
`/api/service/{uid}/call`

Where "{uid}" is the uid of your service. Call your service with user info if the user has enable your service.

You can add a parameter "callback" in order to enable JSONP strategy.

Content-Type: JSON

Ex:
`/api/service/call/SOME-UID?callback=callback`

Result:

```jsonp
callback({
    "status": true,
    "code": 0,
    "data": {"MyService": "MyValue"},
    "error": null
});
```

