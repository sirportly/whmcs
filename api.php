<?PHP
include("../../../configuration.php");
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/adminfunctions.php");
include("../../../includes/countries.php");
include_once("sirportly.php");

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Sirportly"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

// Check to see for valid admin credentials 
$admin  = select_query("tbladmins", "id", array("username" => $_SERVER['PHP_AUTH_USER'], "password" => md5($_SERVER['PHP_AUTH_PW']) ));
if ( mysql_num_rows($admin) ) {
	$admin = mysql_fetch_array($admin);
} else {
	header('HTTP/1.1 404 Not Found');
	die;
}

logActivity("Datasource requested for ".$_REQUEST['type']." ".$_REQUEST['data']);

// get cliend details from db
$client = select_query('tblclients', '', array('email' => $_REQUEST['data']));

// check for valid client email
if (mysql_num_rows($client)) {
	$client = mysql_fetch_array($client);
} else {
	header('HTTP/1.1 404 Not Found');
	die;
}

$current_fields = array();
$result = mysql_query("SELECT * FROM `sirportly`");
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
  $current_fields[$row['1']][$row[2]] = $row['2'];
}



// prepare return
global $CONFIG;
$apiresults = array();
$decrypt = array('password', 'securityqans');
$apiresults['contact_methods']['email'] = array($client['email']);

foreach ($current_fields as $key => $value) {
  $id = ($key == 'tblclients' ? 'id' : 'userid');
  
  $query = select_query($key,implode(',',$value),array($id => $client['id']));
  if (mysql_num_rows($query) == '1') {
    $result = mysql_fetch_array($query,MYSQL_ASSOC);
    foreach ($value as $row_key => $row_value) {
      if ( in_array($row_key, $decrypt) ) {
        $api = localAPI('decryptpassword', array('password2' => $result[$row_key]), $admin['id']);
        $apiresults[sirportly_lang($key)][$row_key] =  $api['password'];
      }else{
        $apiresults[sirportly_lang($key)][$row_key] = $result[$row_key];
      }
    }
  } else {
    $res = array();
    while ($result = mysql_fetch_array($query,MYSQL_ASSOC)) {
      $row = array();
      foreach ($value as $row_key => $row_value) {
        if ( in_array($row_key, $decrypt) ) {
          $api = localAPI('decryptpassword', array('password2' => $result[$row_key]), $admin['id']);
          $row[$row_key] =  $api['password'];
        }else{
          $row[$row_key] = $result[$row_key];
        }
      }
      array_push($res,$row);      
    }
   $apiresults[sirportly_lang($key)] = $res;
  }
  global $customadminpath;
  
  if ($key == 'tblclients') {
   $apiresults[sirportly_lang('tblclients')]['WHMCS URL'] = 'link:'.$CONFIG['SystemURL'].'/'.$customadminpath.'/clientssummary.php?userid='.$client['id'].'|Click Here';
  } 
}
 
header('HTTP/1.1 200 OK');
echo json_encode($apiresults);

?>