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
  $ca->setPageTitle($_LANG['supportticketspagetitle']);
  $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
  $ca->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
  $ca->addToBreadCrumb('supporttickets.php', $whmcs->get_lang('supportticketspagetitle'));
  $ca->initPage();

  ## Setup the menu contexts
  Menu::addContext('support_module', 'sirportly');

  if ( $sirportlyContact = sirportlyContact($_SESSION['uid'], $_SESSION['cid']) ) {
    $sirportlyTickets = _doSirportlyAPICall('tickets/contact', array(
      'contact' => $sirportlyContact,
      'status_types' => '0,2'
    ));

    $tickets = array();
    foreach ($sirportlyTickets['records'] as $ticket) {
      $tickets[] = array(
        'tid'         => $ticket['reference'],
        'c'           => $ticket['id'],
        'department'  => $ticket['department']['name'],
        'subject'     => $ticket['subject'],
        'statusColor' => '#' . $ticket['status']['colour'],
        'status'      => $ticket['status']['name'],
        'lastreply'   => fromMySQLDate($ticket['last_update_posted_at'], true, true),
      );
    }
    $ca->assign('tickets', $tickets);
  }

  $ca->assign('noSearch', true);
  $ca->assign('noPagination', false);


  $ca->setTemplate('/templates/sirportly/supportticketslist.tpl');

  $ca->output();