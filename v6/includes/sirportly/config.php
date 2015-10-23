<?php

  ## The URL of your Sirportly installation staff interface
  $baseUrl = 'http://atech.sirportly.dev';

  ## The API token to use when talking to Sirportly
  $apiToken = 'dev';

  ## The API secret to use when talking to Sirportly
  $apiSecret = 'dev';

  ## The ID of the brand which this WHMCS installation should be
  ## linked to, this value determines which departments to show
  $BrandId = 2;

  ## The ID of your `resolved` ticket status, set to false to prevent
  ## clients from closing tickets
  $closedStatusId = 4;

  ## The ID of your `new` ticket status
  $newStatusId = 1;

  ## The default WHMCS behaviour is to allow sub accounts access
  ## to view tickets that were opened by the primary contact, set
  ## this option to false to keep this behaviour.
  $canOnlyViewOwnTickets = false;