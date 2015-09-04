<?php

  /**
   * Sirportly WHMCS Support Tickets Module
   * @copyright Copyright (c) 2015 aTech Media Ltd
   * @version 3.0
   */

  use WHMCS\View\Menu\Item as MenuItem;

  ## This doesn't deserve to live here
  if (App::getCurrentFilename() == 'submitticket') {
    add_hook('ClientAreaFooterOutput', 1, function ($vars)
    {

      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      return '<script>
          function refreshCustomFields(input) {
            jQuery("#customFieldsContainer").load(
              "submitticket.php",
                { action: "fetchcustomfields", deptid: $(input).val() }
            );
          }
      </script>';
    });
  }

  if (App::getCurrentFilename() == 'viewticket') {
    add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar)
    {

      ## Required files
      include(ROOTDIR . "/includes/sirportly/config.php");

      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      ## Fetch the ticket
      $sirportlyTicket = Menu::Context('sirportlyTicket');

      $supportPanel = $primarySidebar->addChild('Ticket Information', array(
        'label' => Lang::trans('ticketinfo'),
        'icon'  => 'fa-ticket',
        'class' => 'ticket-details-children'
      ));

      $child = $supportPanel->addChild('Subject', array(
        'label' => "<span class='title'>Subject</span><br>{$sirportlyTicket['subject']}",
        'order' => 1
      ));
      $child->setClass('ticket-details-children');

      $child = $supportPanel->addChild('Department', array(
        'label' => "<span class='title'>Department</span><br>{$sirportlyTicket['department']['name']}",
        'order' => 2
      ));
      $child->setClass('ticket-details-children');

      $submitted_at = fromMySQLDate($sirportlyTicket['submitted_at'], true, true);
      $child = $supportPanel->addChild('Submitted', array(
        'label' => "<span class='title'>Submitted</span><br>{$submitted_at}",
        'order' => 3
      ));
      $child->setClass('ticket-details-children');

      $updated_at = fromMySQLDate($sirportlyTicket['last_update_posted_at'], true, true);
      $child = $supportPanel->addChild('Last_Updated', array(
        'label' => "<span class='title'>Last Updated</span><br>{$updated_at}",
        'order' => 4
      ));
      $child->setClass('ticket-details-children');

      $child = $supportPanel->addChild('Priority', array(
        'label' => "<span class='title'>Priority</span><br>{$sirportlyTicket['priority']['name']}",
        'order' => 5
      ));
      $child->setClass('ticket-details-children');

      ## Footer
      $replyText = Lang::trans('supportticketsreply');

      $ticketClosed = ($sirportlyTicket['status']['status_type'] == '1');
      $showCloseButton = $closedStatusId;
      $class = $showCloseButton ? 'col-xs-6 col-button-left' : 'col-xs-12';

      $footer = '<div class="' . $class . '">
        <button class="btn btn-success btn-sm btn-block" onclick="jQuery(\'#ticketReply\').click()">
          <i class="fa fa-pencil"></i> ' . $replyText . '
        </button>
      </div>';

      if ($showCloseButton) {
        $footer .= '<div class="col-xs-6 col-button-right">
          <button class="btn btn-danger btn-sm btn-block"';

          if ($ticketClosed) {
            $footer .= 'disabled="disabled"><i class="fa fa-times"></i> ' . Lang::trans('supportticketsstatusclosed');
          } else {
            $footer .=  'onclick="window.location=\'?tid=' .  $sirportlyTicket['reference'] . '&amp;c=' . $sirportlyTicket['id'] . '&amp;closeticket=true\'"> <i class="fa fa-times"></i> ' . Lang::trans('supportticketsclose');
          }
        $footer .= '</button></div>';
      }

      $supportPanel->setFooterHtml($footer);
    });
  }

  if (App::getCurrentFilename() == 'viewticket' || App::getCurrentFilename() == 'submitticket' || App::getCurrentFilename() == 'supporttickets') {

    add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar)
    {
      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      $supportPanel = $secondarySidebar->addChild('Support', array(
        'label' => 'Support',
        'icon'  => 'fa-support',
      ));
      $child = $supportPanel->addChild('Tickets', array(
        'label' => 'My Support Tickets',
        'icon'  => 'fa-ticket',
        'uri'   => 'supporttickets.php',
        'order' => 1
      ));
      $child->setClass(App::getCurrentFilename() == 'supporttickets' ? 'active' : '');
      $child = $supportPanel->addChild('Announcements', array(
        'label' => 'Announcements',
        'icon'  => 'fa-list',
        'uri'   => 'announcements.php',
        'order' => 2
      ));
      $child = $supportPanel->addChild('Knowledgebase', array(
        'label' => 'Knowledgebase',
        'icon'  => 'fa-info-circle',
        'uri'   => 'knowledgebase.php',
        'order' => 3
      ));
      $child = $supportPanel->addChild('Downloads', array(
        'label' => 'Downloads',
        'icon'  => 'fa-download',
        'uri'   => 'downloads.php',
        'order' => 4
      ));
      $child = $supportPanel->addChild('Network_Status', array(
        'label' => 'Network Status',
        'icon'  => 'fa-rocket',
        'uri'   => 'serverstatus.php',
        'order' => 5
      ));
      $child = $supportPanel->addChild('Open_Ticket', array(
        'label' => 'Open Ticket',
        'icon'  => 'fa-comments',
        'uri'   => 'submitticket.php',
        'order' => 6
      ));
      $child->setClass(App::getCurrentFilename() == 'submitticket' ? 'active' : '');
    });
  }