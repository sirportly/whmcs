<?PHP
require_once('sirportly_functions.php');
require_once('markdown.php');

function sirportly_tickets($vars)
{
  if ( sirportly_enabled() ) {
    if ( $contact = sirportly_contact() ) {
      $tickets = curl('/api/v2/tickets/contact', array('contact' => $contact, 'page' => $vars['pagenumber']));
      $opntickets = curl('/api/v2/tickets/contact', array('contact' => $contact, 'status_types' => '0,2'));
      $vars['tickets']                            = sirportly_ticket_table($tickets['results']['records']);
      $vars['numtickets']                         = $tickets['results']['pagination']['total_records'];
      $vars['numactivetickets']                   = $tickets['results']['pagination']['total_records'];
      $vars['numopentickets']                     = $opntickets['results']['pagination']['total_records'];
      $vars['clientsstats']['numtickets']         = $tickets['results']['pagination']['total_records'];
      $vars['clientsstats']['numactivetickets']   = $tickets['results']['pagination']['total_records'];
      $vars['numproducts']                        = $tickets['results']['pagination']['total_records'];
      $vars['numitems']                           = $tickets['results']['pagination']['total_records'];
      $vars['nextpage']                           = ($vars['pagenumber'] < $tickets['results']['pagination']['pages'] ? $vars['pagenumber'] + 1 : 0 );
      $vars['prevpage']                           = ($vars['pagenumber'] != 1 ? $vars['pagenumber'] - 1 : 0 );
      $vars['totalpages']                         = $tickets['results']['pagination']['pages'];
    }else{
      $vars['tickets']                            = array();
      $vars['numtickets']                         = '0';
      $vars['numactivetickets']                   = '0';
      $vars['numopentickets']                     = '0';
      $vars['clientsstats']['numtickets']         = '0';
      $vars['clientsstats']['numactivetickets']   = '0';
      $vars['numproducts']                        = '0';
      $vars['numitems']                           = '0';
      $vars['nextpage']                           = '0';
      $vars['totalpages']                         = '1';
    }
    return $vars;
  }
}

function sirportly_link_accounts($vars){
  if ( sirportly_enabled() ) {
    if ( !sirportly_contact() ) {
      $client = mysql_fetch_array(select_query('tblclients', '', array('id' => $_SESSION['uid'])));
      $contact = curl('/api/v2/tickets/spql', array('spql' => "SELECT customers.id, count FROM tickets WHERE customer_contact_methods.method_type = 'email' AND customer_contact_methods.data = '".$client['email']."' LIMIT 1"));
      if ($contact['results']['results']['0']['1']) {
        mysql_query("INSERT INTO `sirportly_customers` (`userid`, `customerid`) VALUES ('".$_SESSION['uid']."', '".$contact['results']['results']['0']['0']."');");
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
			sirportly_admin('/api/v1/customers/edit_contact_method',$sirportly_settings['token'],$sirportly_settings['secret'],$customer);			
		}
	}	
}

function hook_add_new_ticket_link_to_client_summary($vars) {
  ## Fetch the staff interface URL from the database
  $module_data = select_query('tbladdonmodules', 'value', array('module' => 'sirportly', 'setting' => 'staff_url') );
  $module_result = mysql_fetch_array($module_data, MYSQL_ASSOC);
  if ($module_result['value'] != "") {    
    ## Fetch client based on ID
    $user_data = select_query('tblclients', 'email', array('id' => $_REQUEST['userid']) );
    $user_result = mysql_fetch_array($user_data, MYSQL_ASSOC);
    
    return array('<a href="'.$module_result['value'].'/staff/tickets/new?customer_contact='.$user_result['email'].'"><img src="images/icons/ticketsother.png" border="0" align="absmiddle" /> Open New Sirportly Ticket</a>');
  }
}

function sirportly_css()
{
  return '<link href="modules/addons/sirportly/css/style.css" rel="stylesheet">';
}

add_hook("ClientAreaPage",200,"sirportly_tickets");
add_hook("ClientAreaHeadOutput",10,"sirportly_css");
add_hook("AdminAreaClientSummaryActionLinks",1,"hook_add_new_ticket_link_to_client_summary");
add_hook("ClientEdit",111,"update_sirportly_email");
add_hook("ClientAreaPage",111,"sirportly_link_accounts");