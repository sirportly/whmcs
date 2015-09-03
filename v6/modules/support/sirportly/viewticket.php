<?PHP

/**
 * Sirportly WHMCS Support Tickets Module
 * @copyright Copyright (c) 2015 aTech Media Ltd
 * @version 3.0
 */

  use WHMCS\ClientArea;
  use Illuminate\Database\Capsule\Manager as Capsule;

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
  $contact_ids = sirportlyContacts($_SESSION['uid']);

  ## Check to ensure the ticket exists, that the ID matches and the user has access
  if (array_key_exists('error', $response) || $response['id'] != $c || !in_array($response['contact']['id'], $contact_ids)) {
    $ca->assign('invalidTicketId', true);
    $ca->output();
    return;
  }

  class Named_Cart extends WHMCS\View\Client\Menu\PrimarySidebarFactory {
    function sirportlyTicketView() {
    }
  }

  ## Setup the menu contexts
  Menu::addContext('sirportlyTicket', $response);
  Menu::addContext('ticketId', 1);
  Menu::addContext('c', $c);
  Menu::addContext('support_module', 'sirportly');

  Menu::primarySidebar( 'ticketView' );
  Menu::secondarySidebar( 'ticketView' );

  ## Download an attachment
  if ($action == "attachment") {
    $attachment = _doSirportlyAPICall('tickets/attachment', array('ticket' => $tid, 'attachment' => $_GET['aid']));
    echo $attachment;

    exit();
  }

  $sirportlyContact = sirportlyContact($_SESSION['uid'], $_SESSION['cid']);

  ## Close the ticket
  if ($closeticket && $closedStatusId) {
    $response = _doSirportlyAPICall('tickets/update',
      array(
        'ticket' => $tid,
        'status' => $closedStatusId
      )
    );

    if (array_key_exists('errors', $response)) {
      $formattedErrorMessages = formatSirportlyErrors($response['errors']);
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

    ## If we're error free attempt to submit the update to Sirportly
    if (!$errormessage) {
      ## Upload any attachments and store their tokens
      $attachedFiles = sirportly_upload_attachments($_FILES['attachments']);

      ## Submit the update
      $params = array();
      $params['ticket']      = $tid;
      $params['message']     = $replymessage;
      $params['attachments'] = implode($attachedFiles);

      if (!empty($sirportlyContact)) {
        $params['contact'] = $sirportlyContact;
      } else {
        $params['contact_name']        = $clientDetails['fullname'];
        $params['contact_method_data'] = $clientDetails['email'];
        $params['contact_method_type'] = 'email';
      }

      $sirportlyTicketUpdate = _doSirportlyAPICall('tickets/post_update', $params);

      ## Check for any errors
      if (array_key_exists('errors', $sirportlyTicketUpdate)) {
        $formattedErrorMessages = formatSirportlyErrors($sirportlyTicketUpdate['errors']);
        $ca->assign('errormessage', $formattedErrorMessages);
      } else {

        ## Check to see if we need to store the contact_id
        if (empty($sirportlyContact)) {
          sirportlyStoreContact($_SESSION['uid'], $_SESSION['cid'], $sirportlyTicketUpdate['contact']['id']);
        }
        redir("tid=" . $tid . "&c=" . $c);
      }
    }

    $ca->assign('postingReply', isset($postreply));
    $ca->assign('errormessage', $errormessage);

  }

  ## Setup the updates
  $updates = array();
  foreach ($response['updates'] as $key => $update) {

    ## Sort the attachments
    $attachments = array();
    foreach ($update['attachments'] as $attachment) {
      $attachments[] = array('id' => $attachment['id'], 'name' => $attachment['name']);
    }

    $updates[] = array(
      'id'          => $update['id'],
      'admin'       => $update['author']['type'] == 'User',
      'date'        => fromMySQLDate($update['posted_at'], true, true),
      'name'        => $update['from_name'],
      'contactid'   => null,
      'userid'      => '1',
      'message'     => nl2br($update['message']),
      'attachments' => $attachments
    );
  }

  $smarty->assign("ascreplies", $updates);
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