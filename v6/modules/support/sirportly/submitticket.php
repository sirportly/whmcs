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
  include(ROOTDIR . "/includes/sirportly/config.php");

  $ca = new ClientArea();
  $ca->setPageTitle($_LANG['supportticketssubmitticket']);
  $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
  $ca->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
  $ca->addToBreadCrumb('supporttickets.php', $whmcs->get_lang('supportticketspagetitle'));
  $ca->addToBreadCrumb('submitticket.php', $whmcs->get_lang('supportticketssubmitticket'));
  $ca->initPage();

  ## Return custom fields
  if ($action == 'fetchcustomfields') {
    $sirportlyCustomFields = sirportlyCustomFields($deptid, $customfield);
    $ca->assign("customfields", $sirportlyCustomFields);
    echo $smarty->fetch($CONFIG['Template'] . "/supportticketsubmit-customfields.tpl");
    exit();
  }

  ## Setup the menus
  Menu::addContext('support_module', 'sirportly');

  ## Load the sirportly contact
  $sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);

  # Fetch the WHMCS client
  $clientDetails = getClientsDetails($_SESSION['uid'], $_SESSION['cid']);
  $smarty->assign("clientname", $clientDetails['fullname']);
  $smarty->assign("email", $clientDetails['email']);

  ## Departments
  $departments = sirportlyDepartments();
  $ca->assign("departments", $departments);
  $ca->assign("deptid", $deptid);

  ## Priorities
  $priorities = sirportly_priorities();
  $ca->assign("priorities", $priorities);
  $ca->assign("priorityid", $_POST['priorities']);

  ## Custom fields
  $sirportlyCustomFields = sirportlyCustomFields($deptid, $customfield);
  $ca->assign("customfields", $sirportlyCustomFields);

  $ca->assign('errormessage', $validate->getHTMLErrorOutput());
  $ca->assign("allowedfiletypes", $CONFIG['TicketAllowedFileTypes']);
  $ca->assign("subject", $subject);
  $ca->assign("message", $message);

  $ca->assign("captcha", $captcha);
  $ca->assign("recapatchahtml", clientAreaReCaptchaHTML());

  switch ($step) {
    case '2':
      $ca->setTemplate('/templates/sirportly/supportticketsubmit-steptwo.tpl');
    break;

    case '3':
      ## Upload any attachments and store their tokens
      $attachedFiles = sirportly_upload_attachments($_FILES['attachments']);

      ## New ticket params
      $params = array();
      $params['subject']     = $subject;
      $params['status']      = $newStatusId;
      $params['priority']    = $_POST['priorities'];
      $params['department']  = $deptid;
      $params['contact']     = $sirportlyContact;

      ## Custom field params
      foreach ($customfield as $cf => $value) {
        $params["custom[{$cf}]"]    = $value;
      }

      ## Submit the ticket to Sirportly
      $sirportlyTicket = _doSirportlyAPICall('tickets/submit', $params);

      ## Check to see if we encountered any errors
      if ( checkForSirportlyErrors($sirportlyTicket) ) {
        $step = 2;
        $formattedErrorMessages = formatSirportlyErrors($ticket['errors']);
        $ca->assign('errormessage', $formattedErrorMessages);
        $ca->setTemplate('/templates/sirportly/supportticketsubmit-steptwo.tpl');
        $ca->output();
        return;
      }

      ## Add the first update
      $sirportlyTicketUpdate = _doSirportlyAPICall('tickets/post_update', array(
        'ticket'        => $sirportlyTicket['reference'],
        'authenticated' => true,
        'attachments'   => implode($attachedFiles),
        'contact'       => $sirportlyTicket['contact']['id'],
        'message'       => $message
      ));

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