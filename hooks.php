<?PHP
require_once('sirportly_functions.php');
require_once('markdown.php');


function sirportly_redirect_if_enabled()
{
  if (basename($_SERVER['SCRIPT_NAME']) == 'submitticket.php' && sirportly_enabled()) {
    header('Location: supporttickets.php?step=1');
  }
}

function sirportly_step_2()
{
  if (basename($_SERVER['SCRIPT_NAME']) == 'supporttickets.php' && $_GET['step'] == '2' && sirportly_enabled()) {
    if (!$_SESSION['uid']) { header('Location: login.php'); exit; }

    $client = mysql_fetch_array(select_query('tblclients', '', array('id' => (int)$_SESSION['uid'])),MYSQL_ASSOC);
    global $smarty;
    $sirportly_settings = sirportly_settings();
    $token = $sirportly_settings['token'];
    $secret = $sirportly_settings['secret'];
    $priority = $sirportly_settings['priority'];
    
    
    if ($_POST) {
      $smarty->assign_by_ref('subject',$_POST['subject']);
      $smarty->assign_by_ref('message',$_POST['message']);
      
      if (!$_POST['subject'] || !$_POST['message']) {
        $errormessage .= '<p class="bold">'.$_LANG['clientareaerrors'].'</p><ul><li>You did not enter a subject or message</li>';
        $smarty->assign_by_ref('errormessage',$errormessage);
      } else {
				$sirportly_customer = select_query('sirportly_customers', '', array('userid' => $_SESSION['uid']));
				
				# start ticket array
				$ticket_array['subject'] 		= $_POST['subject'];
				$ticket_array['status'] 		= $sirportly_settings['status'];
				$ticket_array['priority']		= $_POST['urgency'];
				$ticket_array['brand'] 			= $sirportly_settings['brand'];
				$ticket_array['department'] = $_POST['deptid'];
							
				if (mysql_num_rows($sirportly_customer)) {
					$customer = mysql_fetch_array($sirportly_customer,MYSQL_ASSOC);
					$ticket_array['customer'] = $customer['customerid'];
				} else {
					$ticket_array['name'] =  $client['firstname'].' '.$client['lastname'];
					$ticket_array['email'] = $client['email'];
				}
				
        $ticket = sirportly_submit_ticket($sirportly_settings['token'],$sirportly_settings['secret'],$ticket_array);

        if ($ticket['reference']) {
					sirportly_customer_id($ticket['customer']['id']);
          $reply = sirportly_reply_to_ticket($token,$secret,$ticket['reference'],$_POST['message'],$ticket['customer']['id']);
          if ($reply) {
            header('Location: viewticket.php?c='.$ticket['id'].'&tid='.$ticket['reference']);
          }
        }      
      }
    }
    
    $departments = sirportly_clientarea_departments($sirportly_settings['token'],$sirportly_settings['secret'],$sirportly_settings['brand']);
    $priorities = sirportly_priorities($sirportly_settings['token'],$sirportly_settings['secret']);
    $priorityid = ($_POST['priority'] ? $_POST['priority'] : $priority);

    $smarty->assign_by_ref('departments',$departments);
    $smarty->assign_by_ref('priorities',$priorities);
    $smarty->assign_by_ref('deptid',$_GET['deptid']);
    $smarty->assign_by_ref('priorityid',$priorityid);
    $template = $smarty->get_template_vars('template');
    
    $smarty->display($template.'/header.tpl');
    $smarty->display('../modules/addons/sirportly/templates/step_two.tpl');
    $smarty->display($template.'/footer.tpl');
    exit;
  }
}

function sirportly_post_reply(){
  
  $sirportly_settings = sirportly_settings();
  if (basename($_SERVER['SCRIPT_NAME']) == 'viewticket.php' && sirportly_enabled() && $_GET['c'] && $_GET['tid'] && $_POST['replymessage']) {
    if (!$_SESSION['uid']) {
      header('Location: login.php');
      exit;
    }

		$customerid = mysql_fetch_array(select_query('sirportly_customers', '', array('userid' => $_SESSION['uid'])), MYSQL_ASSOC);
    
     global $smarty;
     $sirportly_settings = sirportly_settings();
     $reply = sirportly_reply_to_ticket($sirportly_settings['token'],$sirportly_settings['secret'],$_GET['tid'],$_POST['replymessage'],$customerid['customerid']);

     if (!$reply) {
       $errormessage = 'An error occurred whilst posting your reply, please try again.';
     }
     $smarty->assign_by_ref('errormessage',$errormessage);
  }
}

function sirportly_clientarea_viewticket(){
  
  if (basename($_SERVER['SCRIPT_NAME']) == 'viewticket.php' && sirportly_enabled() && $_GET['c'] && $_GET['tid']) {
    if (!$_SESSION['uid']) { header('Location: login.php'); exit; }
    $sirportly_settings = sirportly_settings();
    global $smarty;

    $template = $smarty->get_template_vars('template');
    $error = false;
    $smarty->assign_by_ref('error',$error);
    $ticket = sirportly_view_ticket($sirportly_settings['token'],$sirportly_settings['secret'],$_GET['tid']);
    if ($ticket['id'] == $_GET['c'] && $ticket['reference'] == $_GET['tid']) {
      $status = '<span style="color:#'.$ticket['status']['colour'].'">'.$ticket['status']['name'].'</span>';
      $smarty->assign_by_ref('status',$status);
      $urgency = '<span style="color:#'.$ticket['priority']['colour'].'">'.$ticket['priority']['name'].'</span>';
      $smarty->assign_by_ref('urgency',$urgency);
      $smarty->assign_by_ref('department',$ticket['department']['name']);
			$smarty->assign_by_ref('subject',$ticket['subject']);
      $smarty->assign_by_ref('date',fromMySQLDate($ticket['submitted_at'],'time'));
      $smarty->assign_by_ref('tid',$ticket['reference']);
      $smarty->assign_by_ref('c',$ticket['id']);
      $smarty->assign('showclosebutton', $sirportly_settings['close_ticket']);
    
      foreach ($ticket['updates'] as $key => $value){ 
        if (!$value['private']) {
          $replies[] = array(
            'name' => ($value['author']['name'] ? $value['author']['name'] : $value['author']['first_name'].' '.$value['author']['last_name']),
            'message' => nl2br(($value['message'] ? $value['message'] : strip_tags($value['html_body']) ).($value['signature_text'] ? "<div class='signature'><p>".$value['signature_text']."</p></div>" : "") ),
            'admin' => ($value['author']['type'] == 'User' ? true : false),
            'date' => fromMySQLDate($value['posted_at'],'time'),
            );
        }
      }

      $smarty->assign_by_ref('descreplies',array_reverse($replies));
      $smarty->display($template.'/header.tpl');
      $smarty->display('../modules/addons/sirportly/templates/view_ticket.tpl');
      $smarty->display($template.'/footer.tpl');
      exit;
    } else {
      header('Location: supporttickets.php');
      exit;
    }
  }
}



function sirportly_tickets()
{
  if (basename($_SERVER['SCRIPT_NAME']) == 'supporttickets.php' && sirportly_enabled()) {
    if (!$_SESSION['uid']) { header('Location: login.php'); exit; }
    
    
    $sirportly_settings = sirportly_settings();
    
    global $smarty;
		$template = $smarty->get_template_vars('template');
		
		$sirportly_customer = select_query('sirportly_customers', '', array('userid' => $_SESSION['uid']));
		if (!mysql_num_rows($sirportly_customer)) {
			$smarty->assign('tickets', array());
			$smarty->assign('nextpage', '0');
			$smarty->assign('prevpage', '0');
			$smarty->assign('numtickets', '0');
			$smarty->assign('totalpages', '1');
		} else {
			$sirportly_customer = mysql_fetch_array($sirportly_customer,MYSQL_ASSOC);
			$total_tickets = sirportly_spql($sirportly_settings['token'],$sirportly_settings['secret'],"SELECT count FROM tickets WHERE customers.id = '".$sirportly_customer['customerid']."'");
			$tickets = sirportly_clientarea_tickets($sirportly_settings['token'],$sirportly_settings['secret'], $sirportly_customer['customerid'],$sirportly_settings['brand']);
			$smarty->assign_by_ref('tickets',$tickets);
			$smarty->assign_by_ref('numopentickets',$number_of_tickets);
			$smarty->assign_by_ref('numtickets',count($tickets));
			$smarty->assign('totalpages', '1');
		}
		
		$smarty->display($template.'/header.tpl');
	  $smarty->display('../modules/addons/sirportly/templates/supportticketlist.tpl');
	  $smarty->display($template.'/footer.tpl');
	  exit;
  }
}

function sirportly_step_1()
{  
  if (basename($_SERVER['SCRIPT_NAME']) == 'supporttickets.php' && $_GET['step'] == '1' && sirportly_enabled()) {
    if (!$_SESSION['uid']) {
      header('Location: login.php');
      exit;
    }
    $sirportly_settings = sirportly_settings();
    global $smarty;
    $template = $smarty->get_template_vars('template');
    $departments = sirportly_clientarea_departments($sirportly_settings['token'],$sirportly_settings['secret'],$sirportly_settings['brand']);
    $smarty->assign_by_ref('departments', $departments);
    $smarty->display($template.'/header.tpl');
    $smarty->display('../modules/addons/sirportly/templates/step_one.tpl');
    $smarty->display($template.'/footer.tpl');
    exit;
  }
}


function sirportly_clientstats()
{
  if (sirportly_enabled() && $_SESSION['uid'] ) {
    global $smarty;
		$customerid = mysql_fetch_array(select_query('sirportly_customers', '', array('userid' => $_SESSION['uid'])), MYSQL_ASSOC);
    $sirportly_settings = sirportly_settings();
    $clientsstats = $smarty->get_template_vars('clientsstats');
    $query = "SELECT COUNT FROM tickets WHERE customers.id = '".$customerid['customerid']."' AND brands.id = '".$sirportly_settings['brand']."'";
    $tickets = sirportly_spql($sirportly_settings['token'],$sirportly_settings['secret'],$query);
    $clientsstats['numtickets'] = $tickets['results']['0']['0'];
    $smarty->assign_by_ref('clientsstats',$clientsstats);
  }
}

function sirportly_client_area()
{  
  if (sirportly_enabled() && $_SESSION['uid'] ) {
    global $smarty;
    $sirportly_settings = sirportly_settings();
    $sirportly_customer = select_query('sirportly_customers', '', array('userid' => $_SESSION['uid']));
    $sirportly_customer = mysql_fetch_array($sirportly_customer,MYSQL_ASSOC);
    $open_tickets = sirportly_open_tickets($sirportly_settings['token'],$sirportly_settings['secret'], $sirportly_customer['customerid'], $sirportly_settings['brand']);
    $clientsstats = $smarty->get_template_vars('clientsstats');
		$clientsstats['numactivetickets'] = count($open_tickets);
    $smarty->assign_by_ref('tickets', $open_tickets);
		$smarty->assign_by_ref('clientsstats',$clientsstats);
  }  
}

function sirportly_link_accounts(){
	if ( sirportly_enabled() && $_SESSION['uid'] ) {
		$sirportly_customer = select_query('sirportly_customers', '', array('userid' => $_SESSION['uid']));
		$client = mysql_fetch_array(select_query('tblclients', '', array('id' => $_SESSION['uid'])));
		if (!mysql_num_rows($sirportly_customer)) {
			$sirportly_settings = sirportly_settings();
	    $customer_id = sirportly_spql($sirportly_settings['token'],$sirportly_settings['secret'],"SELECT customers.id, count FROM tickets WHERE customer_contact_methods.method_type = 'email' AND customer_contact_methods.data = '".$client['email']."' lIMIT 1");
			if ($customer_id['results']['0']['1']) {
				mysql_query("INSERT INTO `sirportly_customers` (`userid`, `customerid`) VALUES ('".$_SESSION['uid']."', '".$customer_id['results']['0']['0']."');");
			}			
		}
	}
}

# when a client updates the email in WHMCS update Sirportly
function update_sirportly_email($vars){	
	if ( sirportly_enabled() ) {		
		$sirportly_customer = select_query('sirportly_customers', '', array('userid' => $vars['userid']));
		if (mysql_num_rows($sirportly_customer)) {
			$sirportly_customer = mysql_fetch_array($sirportly_customer, MYSQL_ASSOC);
			$sirportly_settings = sirportly_settings();
			$customer['customer'] = $sirportly_customer['customerid'];
			$customer['method']		= $vars['olddata']['email'];
			$customer['data'] 		= $vars['email'];
			sirportly_api('/api/v1/customers/edit_contact_method',$sirportly_settings['token'],$sirportly_settings['secret'],$customer);			
		}
	}	
}

function sirportly_close_ticket(){
  $sirportly_settings = sirportly_settings();
  if (basename($_SERVER['SCRIPT_NAME']) == 'viewticket.php' && sirportly_enabled() && $_GET['c'] && $_GET['tid'] && $_GET['closeticket'] && $sirportly_settings['close_ticket']) {
    if (!$_SESSION['uid']) { header('Location: login.php'); exit; }
      
      sirportly_api('/api/v1/tickets/update',$sirportly_settings['token'],$sirportly_settings['secret'],array('ticket' => $_GET['tid'],'status' => $sirportly_settings['closed_status']));
  }
}

/*
Knowledgebase hooks
*/

function knowledgebase_page($vars){
  $sirportly_settings = sirportly_settings();
  if (basename($vars['SCRIPT_NAME']) == 'knowledgebase.php' && sirportly_enabled() && $sirportly_settings['kb'] != '') {
    global $CONFIG;
   
    $kb_page = sirportly_api('/api/v1/knowledge/page',$sirportly_settings['token'],$sirportly_settings['secret'],array('kb' => $sirportly_settings['kb'], 'path' => $_GET['page']));
     global $smarty;
    
    $template = $smarty->get_template_vars('template');
    $smarty->assign('page', Markdown($kb_page['page']['content']));
   
    $smarty->display($template.'/header.tpl');
    $smarty->display('../modules/addons/sirportly/templates/knowledgebase.tpl');
    $smarty->display($template.'/footer.tpl');
    exit;
    
  }
}


function knowledgebase_menu($vars){
  $sirportly_settings = sirportly_settings();
  if (basename($vars['SCRIPT_NAME']) == 'knowledgebase.php' && sirportly_enabled() && $sirportly_settings['kb'] != '') {
    global $CONFIG, $smarty;
  
    
    $pages = sirportly_api('/api/v1/knowledge/tree',$sirportly_settings['token'],$sirportly_settings['secret'],array('kb' => $sirportly_settings['kb']));
  
  
  #treeview
  $html .= '<ul id="browser" class="filetree">';
    foreach ($pages as $root_key => $root_value) {
      if (empty($root_value['children'])) {
        $html .= '<li><span class="file"><a href="knowledgebase.php">'.$root_value['title'].'</a></span></li>';
      } else {
        $html .= '<ul><li><span class="folder"><a href="knowledgebase.php">'.$root_value['title'].'</a></span><ul>';
        foreach ($root_value['children'] as $first_key => $first_value) {
          if (empty($first_value['children'])) {
            $html .= '<li><span class="file"><a href="knowledgebase.php?page='.$first_value['permalink'].'">'.$first_value['title'].'</a></span></li>';
          } else {
            $html .= '<ul><li><span class="folder"><a href="knowledgebase.php?page='.$first_value['permalink'].'">'.$first_value['title'].'</a></span><ul>';
            foreach ($first_value['children'] as $second_key => $second_value) {
              if (empty($second_value['children'])) {
                $html .= '<li><span class="file"><a href="knowledgebase.php?page='.$first_value['permalink'].'/'.$second_value['permalink'].'">'.$second_value['title'].'</a></span></li>';
              } else {
                $html .= '<ul><li><span class="folder"><a href="knowledgebase.php?page='.$first_value['permalink'].'/'.$second_value['permalink'].'">'.$second_value['title'].'</a></span><ul>';
                foreach ($second_value['children'] as $third_key => $third_value) {
                  if (empty($third_value['children'])) {
                    $html .= '<li><span class="file"><a href="knowledgebase.php?page='.$first_value['permalink'].'/'.$second_value['permalink'].'/'.$third_value['permalink'].'">'.$third_value['title'].'</a></span></li>';
                  } else {
                    $html .= '<ul><li><span class="folder"><a href="knowledgebase.php?page='.$first_value['permalink'].'/'.$second_value['permalink'].'/'.$third_value['permalink'].'">'.$third_value['title'].'</a></span><ul>';
                    foreach ($third_value['children'] as $fourth_key => $fourth_value) {
                      if (empty($fourth_value['children'])) {
                        $html .= '<li><span class="file"><a href="knowledgebase.php?page='.$first_value['permalink'].'/'.$second_value['permalink'].'/'.$third_value['permalink'].'/'.$fourth_value['permalink'].'">'.$fourth_value['title'].'</a></span></li>';
                      }      
                    }
                    $html .= '</li></ul>';
                  }      
                }
                $html .= '</li></ul>';
              }      
            }
            $html .= '</li></ul>'; 
          }      
        }
        $html .= '</li></ul>';
      }      
    }
    $smarty->assign_by_ref('menu', $html);
  }
}


add_hook("ClientAreaHeadOutput",121,"knowledgebase_page");
add_hook("ClientAreaHeadOutput",120,"knowledgebase_menu");







add_hook("ClientEdit",111,"update_sirportly_email");

add_hook("ClientAreaPage",112,"sirportly_close_ticket");

add_hook("ClientAreaPage",111,"sirportly_link_accounts");
add_hook("ClientAreaPage",111,"sirportly_clientstats");

add_hook("ClientAreaPage",113,"sirportly_clientarea_viewticket");
add_hook("ClientAreaPage",111,"sirportly_post_reply");
add_hook("ClientAreaPage",112,"sirportly_redirect_if_enabled");
add_hook("ClientAreaPage",112,"sirportly_tickets");
add_hook("ClientAreaPage",100,"sirportly_step_1"); #112
add_hook("ClientAreaPage",112,"sirportly_step_2");

add_hook("ClientAreaPage",113,"sirportly_client_area");
?>