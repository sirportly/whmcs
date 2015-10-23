<?PHP

/**
 * Sirportly WHMCS Support Tickets Module
 * @copyright Copyright (c) 2015 aTech Media Ltd
 * @version 3.0
 */

  use WHMCS\ClientArea;

  define("CLIENTAREA", true);

  ## Required files
  require_once(ROOTDIR . "/includes/sirportly/functions.php");
  include_once(ROOTDIR . "/includes/sirportly/config.php");

  $ca = new ClientArea();
  $ca->setPageTitle($_LANG['supportticketsviewticket']);
  $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
  $ca->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
  $ca->addToBreadCrumb('supporttickets.php', $whmcs->get_lang('supportticketspagetitle'));
  $ca->addToBreadCrumb("viewticket.php", $whmcs->get_lang('supportticketsviewticket'));
  $ca->initPage();
  $ca->setTemplate('/templates/sirportly/viewticket.tpl');
  $ca->requireLogin();

  ## Fetch the ticket from Sirportly
  $response = _doSirportlyAPICall('tickets/ticket', array('reference' => $tid));

  ## Fetch an array of sirportly contact_ids
  $contact_ids = sirportlyContacts($_SESSION['uid'], $_SESSION['cid']);

  ## Check to see if we encountered any errors
  if ( checkForSirportlyErrors($response) ) {
    $ca->assign('invalidTicketId', true);
    $ca->output();
    return;
  }

  ## Check to ensure that the ID matches and the user has access
  if ($response['id'] != $c || !in_array($response['contact']['id'], $contact_ids)) {
    $ca->assign('invalidTicketId', true);
    $ca->output();
    return;
  }

  ## Setup the menus
  Menu::addContext('sirportlyTicket', $response);
  Menu::addContext('support_module', 'sirportly');

  ## Download an attachment
  if ($action == "attachment") {
    $ticketUpdate   = _doSirportlyAPICall('ticket_updates/info', array('ticket' => $tid, 'update' => $_GET['id']));
    $attachmentData = _doSirportlyAPICall('tickets/attachment', array('ticket' => $tid, 'attachment' => $_GET['aid']), false);

    ## Locate the details for the attachment
    foreach ($ticketUpdate['attachments'] as $attachment) {
      if ($attachment['id'] == $_GET['aid']) {
        $attachmentDetails = $attachment;
      }
    }

    header("Content-Disposition: attachment; filename=\"{$attachmentDetails['name']}\"");
    header("Content-Type: {$attachmentDetails['content_type']}");
    header("Content-Length: " . strlen($attachmentData));
    header("Connection: close");
    echo $attachmentData;
    exit();
  }

  ## Load the Sirportly contact
  $sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);

  ## Close the ticket
  if ($closeticket && $closedStatusId) {
    $response = _doSirportlyAPICall('tickets/update',
      array(
        'ticket' => $tid,
        'status' => $closedStatusId
      )
    );

    ## Check to see if we encountered any errors
    if ( checkForSirportlyErrors($response) ) {
      $formattedErrorMessages = formatSirportlyErrors($update['errors']);
      $ca->assign('errormessage', $formattedErrorMessages);
    } else {
      redir("tid=" . $tid . "&c=" . $c);
    }
  }

  ## Add an update to the ticket
  if ($postreply) {
    check_token();

    if (!$replymessage) {
      $errormessage .= "<li>" . $_LANG['supportticketserrornomessage'];
    }

    $ca->assign('postingReply', isset($postreply));
    $ca->assign('errormessage', $errormessage);

    ## If we're error free attempt to submit the update to Sirportly
    if (!$errormessage) {
      ## Upload any attachments and store their tokens
      $attachedFiles = sirportly_upload_attachments($_FILES['attachments']);

      ## Submit the update
      $sirportlyTicketUpdate = _doSirportlyAPICall('tickets/post_update', array(
        'ticket'      => $tid,
        'message'     => $replymessage,
        'attachments' => implode($attachedFiles),
        'contact'     => $sirportlyContact
      ));

      ## Check to see if we encountered any errors
      if ( checkForSirportlyErrors($sirportlyTicketUpdate) ) {
        $formattedErrorMessages = formatSirportlyErrors($update['errors']);
        $ca->assign('errormessage', $formattedErrorMessages);
      } else {
        redir("tid=" . $tid . "&c=" . $c);
      }
    }
  }

  ## Setup the updates
  $updates = array();
  foreach ($response['updates'] as $key => $update) {

    ## Sort the attachments
    $attachments = array();
    foreach ($update['attachments'] as $attachment) {
      $attachments[] = array('id' => $attachment['id'], 'name' => $attachment['name']);
    }

    ## Locate the update author type
    $sirportlyUpdateAuthor = locateSirportlyUpdateAuthor($update['author']['id']);

    $updates[] = array(
      'id'          => $update['id'],
      'admin'       => $update['author']['type'] == 'User',
      'date'        => fromMySQLDate($update['posted_at'], true, true),
      'name'        => $update['from_name'],
      'contactid'   => $sirportlyUpdateAuthor['contact_id'],
      'userid'      => $sirportlyUpdateAuthor['user_id'],
      'message'     => nl2br($update['message']),
      'attachments' => $attachments
    );
  }

  krsort($updates);
  $smarty->assign("descreplies", $updates);
  $ca->assign("c", $c);
  $ca->assign("tid", $tid);
  $ca->assign("allowedfiletypes", $CONFIG['TicketAllowedFileTypes']);
  $ca->assign("replymessage", $replymessage);
  $ca->assign('closedticket', $response['status']['status_type'] == 1);

  ## Fetch the client details
  $clientDetails = getClientsDetails($_SESSION['uid'], $_SESSION['cid']);
  $smarty->assign("clientname", $clientDetails['fullname']);
  $smarty->assign("email", $clientDetails['email']);

  ## We don't currently support ratings
  $ca->assign('ratingenabled', false);

  ## Output the template
  $ca->output();