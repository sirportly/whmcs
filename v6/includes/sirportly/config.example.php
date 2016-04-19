<?php

  ## The URL of your Sirportly installation staff interface
  $baseUrl = 'https://example.sirportly.com';

  ## The API token to use when talking to Sirportly
  $apiToken = '';

  ## The API secret to use when talking to Sirportly
  $apiSecret = '';

  ## The ID of the brand which this WHMCS installation should be
  ## linked to, this value determines which departments to show
  $BrandId = 1;

  ## The ID of your `resolved` ticket status, set to false to prevent
  ## clients from closing tickets
  $closedStatusId = 1;

  ## The ID of your `new` ticket status
  $newStatusId = 1;

  ## The ID of the default priority when submitting tickets
  $newPriorityId = 1;

  ## The default WHMCS behaviour is to allow sub accounts access
  ## to view tickets that were opened by the primary contact, set
  ## this option to false to keep this behaviour.
  $canOnlyViewOwnTickets = false;

  ## The key provided to Sirportly for accessing your data frame, be sure to set this
  ## to a hard to guess value.
  $sirportlyFrameKey = 'LONG-RANDOM-STRING';