<?PHP
include("../../../configuration.php");
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/adminfunctions.php");
include("../../../includes/clientareafunctions.php");
include("../../../includes/countries.php");
include_once("sirportly.php");



// Check to see for valid token
$admin  = select_query("tbladdonmodules", "value", array('module' => 'sirportly', 'setting' => 'frame_key', 'value' => $_REQUEST['key'] ));
if ( !mysql_num_rows($admin) ) {
  echo "Invalid key specified";
	die;
}


global $CONFIG;

foreach ($_POST['contacts'] as $key => $value) {
  $split = explode(':',$value);
  if ($split['0'] == 'email') {
    
    ##  Try and find the user based upon the email address given by Sirportly
    $client = select_query('tblclients', 'id', array('email' => $split['1']) );
    if (mysql_num_rows($client)) {
      break;
    }
    
  }
}

if (mysql_num_rows($client)) {
  $client = mysql_fetch_array($client, MYSQL_ASSOC);
} else {
  die('No such user');
}

## Access the internal API, we stupidly require an administrators id so let's fetch the first one we find ...
$administrator = mysql_fetch_array( full_query("SELECT `id` FROM `tbladmins` LIMIT 0, 1"), MYSQL_ASSOC );


## Client Details
$results = localAPI('getclientsdetails',array('clientid' => $client['id'], 'stats' => true),$administrator['id']);
foreach ($results as $key => $value) {
 $vars[$key] = $value;
}

## Client Products
$client_products = localAPI('getclientsproducts',array('clientid' =>$client['id']),$administrator['id']);
foreach ($client_products['products'] as $key => $value) {
 $vars['products'] = $value;
}
print_r($vars);
initialiseClientArea();
echo processSingleTemplate('/modules/addons/sirportly/templates/frame.tpl', $vars);