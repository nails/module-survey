# Events
> Documentation is a WIP.


This module exposes the following events through the [Nails Events Service](https://github.com/nails/common/blob/master/docs/intro/events.md) in the `nails/module-survey` namespace.

> Remember you can see all events available to the application using `nails events`


- [Response](#response)
    - [Nails\Survey\Events::RESPONSE_OPEN](#response-open)
    - [Nails\Survey\Events::RESPONSE_SUBMITTED](#response-submitted)



## Response

<a name="response-open"></a>
### `Nails\Survey\Events::RESPONSE_OPEN `

Fired when a response is set as OPEN

**Receives:**

> ```
> int $iId The ID of the response
> ```


<a name="response-submitted"></a>
### `Nails\Survey\Events::RESPONSE_SUBMITTED `

Fired when a response is set as SUBMITTED

**Receives:**

> ```
> int $iId The ID of the response
> ```
