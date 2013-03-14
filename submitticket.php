<?PHP
define("CLIENTAREA",true);
require("init.php");
$ca = new WHMCS_ClientArea();
$ca->initPage();
$ca->setPageTitle( $whmcs->get_lang('supportticketssubmitticket') );
$settings = sirportly_settings();

if ( !sirportly_enabled() ) {
  die('Sirportly module not enabled.');
}

switch ($_GET['step']) {
  case '2':
    ## Default department, priority
    $ca->assign('deptid', $_GET['deptid'] );
    $ca->assign('priorityid', $settings['priority'] );
  
    if ($_POST) {
      ## Submitted new ticket
      foreach ($_POST as $key => $value) {
        $ca->assign($key, $value);
      }
      
      if ( !$_POST['message'] ) {
        $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornomessage') );
      } elseif( $contact = sirportly_contact() ) {
        $ticket = curl('/api/v2/tickets/submit', array('subject' => $_POST['subject'], 'priority' => $_POST['priorityid'],  'department' => $_POST['deptid'], 'contact' => $contact, 'status' => $settings['status'], 'message' => $_POST['message'] ));
        if ($ticket['status'] != 201) {
          foreach ($ticket['results']['errors'] as $key => $value) {
            $errors .= "<li>".preg_replace("/_id$/", "", ucfirst($key) )." ".$value['0']."</li>";
          }
          $ca->assign('errormessage', $errors );
        } else {
          $_SESSION['tid'] = $ticket['results']['reference'];
          $_SESSION['c'] = $ticket['results']['id'];
          header('Location: submittickets.php?step=3');
        }
      } elseif( !$_POST['name'] ) {
        $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornoname') );
      } elseif( !$_POST['email'] ) {
        $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornoemail') );
      } elseif( !$_POST['subject'] ) {
        $ca->assign('errormessage', $whmcs->get_lang('supportticketserrornosubject') );
      } else {
        $ticket = curl('/api/v2/tickets/submit', array('subject' => $_POST['subject'], 'priority' => $_POST['priorityid'],  'department' => $_POST['deptid'], 'name' => $_POST['name'], 'email' => $_POST['email'], 'status' => $settings['status'], 'message' => $_POST['message'] ));
        if ($ticket['status'] != 201) {
          foreach ($ticket['results']['errors'] as $key => $value) {
            $errors .= "<li>".preg_replace("/_id$/", "", ucfirst($key) )." ".$value['0']."</li>";
          }
          $ca->assign('errormessage', $errors );
        } else {
          $_SESSION['tid'] = $ticket['results']['reference'];
          $_SESSION['c'] = $ticket['results']['id'];
          header('Location: submittickets.php?step=3');
        }
      }
    }
  
    ## Logged in?
    if ( $ca->isLoggedIn() ) {
      $result = mysql_query("SELECT CONCAT_WS(' ', firstname, lastname) as full_name, email FROM tblclients WHERE id=".$ca->getUserID());
      $client = mysql_fetch_array($result, MYSQL_ASSOC);
      $ca->assign('email', $client['email'] );
      $ca->assign('clientname', $client['full_name'] );
    }
    
    ## Fetch the departments
    $departments = curl('/api/v2/objects/departments');
    $dept = array();
    foreach ($departments['results'] as $key => $value) {
      if (!$value['private']) {
        $dept[$value['id']] = array('id' => $value['id'], 'name' => $value['name']);
      }
    }
    
    ## Fetch the statuses
    $priorities = curl('/api/v2/objects/priorities');
    $priority = array();
    foreach ($priorities['results'] as $key => $value) {
      $priority[] = array('id' => $value['id'], 'name' => $value['name']);
    }
    
    $ca->assign('department', $dept[($_GET['deptid'] ? $_GET['deptid'] : $_POST['deptid'])]['name']);
    $ca->assign('departments', $dept);
    $ca->assign('priorities', $priority);
    $ca->setTemplate("../../modules/addons/sirportly/templates/{$CONFIG['Template']}/supportticketsubmit-steptwo");
  break;
  
  case '3':
    $ca->assign('tid', $_SESSION['tid']);
    $ca->assign('c', $_SESSION['c']);
    $_SESSION['tid'] = '';
    $_SESSION['c']   = '';
    $ca->setTemplate("supportticketsubmit-confirm");
  break;
  
  default:
    $departments = curl('/api/v2/objects/departments');
    $dept = array();
    foreach ($departments['results'] as $key => $value) {
      if (!$value['private']) {
        $dept[] = array('id' => $value['id'], 'name' => $value['name']);
      }
    }
    $ca->assign('departments', array() );
    $ca->assign('departments', $dept);
    $ca->setTemplate('supportticketsubmit-stepone');
  break;
} 

  $ca->output();