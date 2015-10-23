<?php

define("CLIENTAREA",true);
require("init.php");

$ca = new WHMCS_ClientArea();
$settings = sirportly_settings();
$ca->setPageTitle( $whmcs->get_lang('supportticketsviewticket') );
$ca->initPage();
global $CONFIG;

if ( !sirportly_enabled() ) {
  die('Sirportly module not enabled.');
}

if ( $CONFIG['RequireLoginforClientTickets'] == 'on' && !$ca->isLoggedIn() ) {
  header('Location: login.php');
}

## Close ticket
if ( $settings['close_ticket'] && $_GET['closeticket'] ) {
  $close = curl('/api/v2/tickets/update', array('ticket' => $_GET['tid'], 'status' => $settings['closed_status']));
  if ($close['status'] != 200) {
    $ca->assign('errormessage', "Unable to close ticket, please contact support." );
  } else {
    header('Location: viewticket.php?tid='.$_GET['tid'].'&c='.$_GET['c']);
  }
}

$ticket   = curl('/api/v2/tickets/ticket', array('reference' => $_GET['tid']));

if ( $ticket['results']['id'] != $_GET['c'] ) {
  header('Location: supporttickets.php');
}

## An update has been posted
if ($_POST) {
  foreach ($_POST as $key => $value) {
    $ca->assign($key, $value);
  }
  if( !$_POST['replymessage'] ){
    $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornomessage') );
  }elseif ( $contact = sirportly_contact() ) {
    $update = curl('/api/v2/tickets/post_update', array('ticket' => $ticket['results']['reference'], 'contact' => $contact,  'message' => $_POST['replymessage'] ));
    if ($update['status'] != 201) {
      $ca->assign('errormessage', $update['results']['errors']['base']['0'] );
    } else {
      header('Location: viewticket.php?tid='.$ticket['results']['reference'].'&c='.$ticket['results']['id']);
    }
  }elseif( !$_POST['replyname'] ){
    $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornoname') );
  }elseif( !$_POST['replyemail'] ){
    $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornoemail') );
  }else{
    $update = curl('/api/v2/tickets/post_update', array('author_name' => $_POST['replyname'], 'author_email' => $_POST['replyemail'], 'ticket' => $ticket['results']['reference'], 'message' => $_POST['replymessage'] ));
    if ($update['status'] != 201) {
      $ca->assign('errormessage', $update['results']['errors']['base']['0'] );
    } else {
      header('Location: viewticket.php?tid='.$ticket['results']['reference'].'&c='.$ticket['results']['id']);
    }
  }
}

## Ticket updates
foreach ($ticket['results']['updates'] as $key => $value){ 
  if (!$value['private']) {
    $replies[] = array(
      'name'        => ($value['author']['name'] ? $value['author']['name'] : $value['author']['first_name'].' '.$value['author']['last_name']),
      'message'     => nl2br(($value['message'] ? $value['message'] : strip_tags($value['html_body']) ).($value['signature_text'] ? "<div class='signature'><p>".$value['signature_text']."</p></div>" : "") ),
      'admin'       => ($value['author']['type'] == 'User' ? true : false),
      'userid'      => ($value['author']['type'] == 'User' ? false : true),
      'date'        => fromMySQLDate($value['posted_at'],'time'),
      'user'        => ($value['author']['type'] == 'User' ? "{$value['author']['first_name']} {$value['author']['last_name']}" : "{$value['from_name']}"),
    );
  }
}

## Logged in?
if ( $ca->isLoggedIn() ) {
  $result = mysql_query("SELECT CONCAT_WS(' ', firstname, lastname) as full_name, email FROM tblclients WHERE id=".$ca->getUserID());
  $client = mysql_fetch_array($result, MYSQL_ASSOC);
  $ca->assign('email', $client['email'] );
  $ca->assign('clientname', $client['full_name'] );
}

## Assign vars to the template
$ca->assign('tid', $ticket['results']['reference']);
$ca->assign('subject', $ticket['results']['subject']);
$ca->assign('date', fromMySQLDate($ticket['results']['submitted_at'], time));
$ca->assign('department', $ticket['results']['department']['name']);
$ca->assign('urgency', "<span style='color:#{$ticket['results']['priority']['colour']}'>{$ticket['results']['priority']['name']}</span>");
$ca->assign('status', "<span style='color:#{$ticket['results']['status']['colour']}'>{$ticket['results']['status']['name']}</span>");
$ca->assign('showclosebutton', ($settings['close_ticket'] ? true : false) );
$ca->assign('descreplies', array_reverse( $replies ) );
$ca->assign('replies', array_reverse( $replies ) );
$ca->assign('c', $_GET['c'] );

$ca->setTemplate('viewticket');
$ca->output();