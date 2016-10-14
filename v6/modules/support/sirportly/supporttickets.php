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
  $ca->setPageTitle($_LANG['supportticketspagetitle']);
  $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
  $ca->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
  $ca->addToBreadCrumb('supporttickets.php', $whmcs->get_lang('supportticketspagetitle'));
  $ca->initPage();

  ## Setup the menus
  Menu::addContext('support_module', 'sirportly');

  ## Load the sirportly contact
  $sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);

  ## Fetch an array of sirportly contact_ids
  $contact_ids = sirportlyContacts($_SESSION['uid'], $_SESSION['cid']);

  ## Fetch the tickets
  $sirportlyTickets = sirportlyTickets($contact_ids);

  foreach ($sirportlyTickets['results'] as $ticket) {
    $tickets[] = array(
      'tid'                 => $ticket[1],
      'c'                   => $ticket[0],
      'department'          => $ticket[4],
      'subject'             => $ticket[2],
      'statusColor'         => '#' . $ticket[5],
      'status'              => $ticket[6],
      'lastreply'           => formatTimestamp($ticket[3], true),
      'normalisedLastReply' => $ticket[3]
    );
  }

  $ca->assign('tickets', $tickets);
  $ca->assign('noSearch', true);
  $ca->assign('noPagination', false);

  $ca->setTemplate('/templates/sirportly/supportticketslist.tpl');
  $ca->output();