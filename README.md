# Supported ILS Systems
* Innovative Interfaces: Sierra
* Innovative Interfaces: Polaris
* TLC: Carl.X

# Developer Information

# Live
## Production
* Base URL: https://oaconnector.ebsco-gss.net/
* Setup URL: https://oaconnector.ebsco-gss.net/setup.php
* Branding Setup URL: https://oaconnector.ebsco-gss.net/setup-branding.php

## Test/Dev
* Base URL: ~~https://oaconnector-dev.ebsco-gss.net/~~
* Setup URL: ~~https://oaconnector-dev.ebsco-gss.net/setup.php~~


# Making a New Connector
## Configuration Tool
For now, contact Eric Frierson with the details you are going to need to gather from a customer in order to make the API calls you need to their ILS.  (e.g., an API URL endpoint or domain, an API key, etc.).  Those values will be available to your driver via a configuration file.

## Driver
The driver will accept POST data that includes the user's encrypted login credentials, as well as any information from the library's configuration file.  See an existing driver to see how this is done.  Basically, you get the customer's ID in the POST data, and you use that to locate the configuration file.

Your driver needs to set these SESSION variables:
* valid - set to Y if the login was successful. N otherwise.
* returnData - this is provided in the POST data, simply pass it along as a session variable
* fullname - this is the full name of the logged in user
* attributes - this is an array of key-pair attributes.  Any number of attributes can be set.
* uid - the user's unique identifier from the ILS system
* custid - the custid.  This is provided in the POST data, simply pass it along as a session variable.

Finally, echo out the following in a JSON-encoded string:
* valid - set to Y if the login was successful.  N otherwise.
* returnData - if the login was successful.  This is provided via the POST data, so it can simply be returned back.
* message - if the login was unsuccessful, you can set a user-friendly reason here.  Sometimes this will be provided by the ILS.