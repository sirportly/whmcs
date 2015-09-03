<?PHP

/**
 * Sirportly WHMCS Support Tickets Module
 * @copyright Copyright (c) 2015 aTech Media Ltd
 * @version 3.0
 */

  define("CLIENTAREA", true);

  if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
  }

  ## Required files
  require_once(ROOTDIR . "/includes/sirportly/functions.php");
  include_once(ROOTDIR . "/includes/sirportly/config.php");

  $ca = new WHMCS_ClientArea();
  $ca->setPageTitle($_LANG['supportticketssubmitticket']);
  $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
  $ca->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
  $ca->addToBreadCrumb('supporttickets.php', $whmcs->get_lang('supportticketspagetitle'));
  $ca->addToBreadCrumb('submitticket.php', $whmcs->get_lang('supportticketssubmitticket'));
  $ca->initPage();

  $ca->assign("capatacha", $captcha);

  ## Setup the menu contexts
  Menu::addContext('support_module', 'sirportly');

  ## Return custom fields
  if ($action == 'fetchcustomfields') {
    $sirportlyCustomFields = sirportlyCustomFields($deptid, $customfield);
    $ca->assign("customfields", $sirportlyCustomFields);
    echo $smarty->fetch($CONFIG['Template'] . "/supportticketsubmit-customfields.tpl");
    exit();
  }

  $clientDetails = getClientsDetails($_SESSION['uid'], $_SESSION['cid']);
  $smarty->assign("clientname", $clientDetails['fullname']);
  $smarty->assign("email", $clientDetails['email']);

  ## Departments
  $departments = sirportly_departments();
  $ca->assign("departments", $departments);
  $ca->assign("deptid", $deptid);

  ## Priorities
  $priorities = sirportly_priorities();
  $ca->assign("priorities", $priorities);
  $ca->assign("priorityid", $_POST['priorities']);

  ## Custom fields
  print_r($customfield);
  $sirportlyCustomFields = sirportlyCustomFields($deptid, $customfield);
  $ca->assign("customfields", $sirportlyCustomFields);

  $ca->assign('errormessage', $validate->getHTMLErrorOutput());
  $ca->assign("allowedfiletypes", $CONFIG['TicketAllowedFileTypes']);
  $ca->assign("subject", $subject);
  $ca->assign("message", $message);

  switch ($step) {
    case '2':
      $ca->setTemplate('/templates/sirportly/supportticketsubmit-steptwo.tpl');
    break;

    case '3':
      ## Upload any attachments and store their tokens
      $attachedFiles = sirportly_upload_attachments($_FILES['attachments']);

      $sirportlyContact = sirportlyContact($_SESSION['uid'], $_SESSION['cid']);

      ## Submit the ticket
      $params = array();
      $params['subject']    = $subject;
      $params['status']     = 'New';
      $params['priority']   = $_POST['priorities'];
      $params['department'] = $deptid;

      if (!empty($sirportlyContact)) {
        $params['contact'] = $sirportlyContact;
      } else {
        $params['contact_name']        = $clientDetails['fullname'];
        $params['contact_method_data'] = $clientDetails['email'];
        $params['contact_method_type'] = 'email';
      }

      $sirportlyTicket = _doSirportlyAPICall('tickets/submit', $params);

      ## Check to see if we encountered any errors
      if (array_key_exists('errors', $sirportlyTicket)) {
        $step = 2;
        $errors = $sirportlyTicket['errors'];
        $ca->assign('errormessage', $errors);
        $ca->setTemplate('/templates/sirportly/supportticketsubmit-steptwo.tpl');
        $ca->output();
        return;
      }

      ## Check to see if we need to store the contact_id
      if (empty($sirportlyContact)) {
        sirportlyStoreContact($_SESSION['uid'], $_SESSION['cid'], $sirportlyTicket['contact']['id']);
      }

      ## Get the params ready for the update
      $params = array(
        'ticket' => $sirportlyTicket['reference'],
        'authenticated' => true,
        'attachments' => $attachedFiles,
        'contact' => $sirportlyTicket['contact']['id'],
        'message' => $message
      );

      ## Add the first update
      $sirportlyTicketUpdate = _doSirportlyAPICall('tickets/post_update', $params);

      ## Set the ticket variables
      $_SESSION['tempticketdata'] = array(
        'tid'     => $sirportlyTicket['reference'],
        'c'       => $sirportlyTicket['id'],
        'subject' => $sirportlyTicket['subject']
      );

      ## Redirect to the next step
      redir("step=4");
    break;
    case '4':
      $ca->assign("tid", $_SESSION['tempticketdata']['tid']);
      $ca->assign("c", $_SESSION['tempticketdata']['c']);
      $ca->assign("subject", $_SESSION['tempticketdata']['subject']);
      $ca->setTemplate('/templates/sirportly/supportticketsubmit-confirm.tpl');
    break;

    default:
      $ca->assign("departments", $departments);
      $ca->setTemplate('/templates/sirportly/supportticketsubmit-stepone.tpl');
    break;
  }

 $ca->output();