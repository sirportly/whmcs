<?PHP

  /**
   * Sirportly WHMCS Support Tickets Module
   * @copyright Copyright (c) 2015 aTech Media Ltd
   * @version 3.0
   */

  use WHMCS\ClientArea;

  define("CLIENTAREA", true);

  ## Required files
  require("../../../init.php");
  include("../../../includes/sirportly/config.php");

  ## Don't allow access to the script unless the frame key has been set
  if (!isset($sirportlyFrameKey)) {
    die("Sirportly frame key not specified in your config.php file");
  }

  ## Check to ensure we have been passed a valid token
  if ($_REQUEST['key'] != $sirportlyFrameKey) {
    die("Invalid frame key specified");
  }

  ## Allow users to specify their own template
  if (isset($_REQUEST['template'])) {
    $template = $_REQUEST['template'];
  } else {
    $template = 'frame';
  }

  ## Check to ensure the template exists
  if (!file_exists("../../../templates/sirportly/{$template}.tpl")) {
    die("Invalid template name '{$template}' specified");
  }

  foreach ($_REQUEST['contacts'] as $contact) {
    $split = explode(':', $contact);
    if ($split['0'] == 'email') {
      $client = select_query('tblclients', 'id', array('email' => $split['1']) );
      if (mysql_num_rows($client)) {
        break;
      }
      else {
        $client = select_query('tblcontacts', 'userid', array('email' => $split['1']) );
        if (mysql_num_rows($client)) {
          break;
        }
      }
    }
  }

  if (mysql_num_rows($client)) {
    $client = mysql_fetch_array($client, MYSQL_ASSOC);
    if ($client['userid'])
      $client['id'] = $client['userid'];

  } else {
    die('No such user');
  }

  ## To access the internal API we still stupidly require an administrators id so let's fetch one.
  $administrator = mysql_fetch_array( full_query("SELECT `id` FROM `tbladmins` LIMIT 0, 1"), MYSQL_ASSOC );

  ## Client Details
  $results = localAPI('getclientsdetails', array('clientid' => $client['id'], 'stats' => true), $administrator['id']);
  foreach ($results as $key => $value) {
    $vars[$key] = $value;
  }

  ## Client Products
  $client_products = localAPI('getclientsproducts', array('clientid' =>$client['id']), $administrator['id']);
  foreach ($client_products['products'] as $key => $value) {
    $vars['products'] = $value;
  }

  ## Client domains
  $client_domains = localAPI('getclientsdomains', array('clientid' => $client['id']), $administrator['id']);
  foreach ($client_domains['domains'] as $domain) {
    $vars['domains'] = $domain;
  }

  $ca = new WHMCS_ClientArea();
  $ca->initPage();
  echo $ca->getSingleTPLOutput("/templates/sirportly/{$template}.tpl", $vars);
