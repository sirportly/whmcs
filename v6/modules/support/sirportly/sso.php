<?PHP

/**
 * Sirportly WHMCS Support Tickets Module
 * @copyright Copyright (c) 2016 aTech Media Ltd
 * @version 3.0
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../includes/sirportly/config.php';
require_once '../../../init.php';

if (!$sirportlySSOAdminId) {
  header('HTTP/1.0 403 Forbidden');
  return;
}

$login = localAPI('validatelogin',array('email' => $_REQUEST['username'], 'password2' => $_REQUEST['password']), $sirportlySSOAdminId);

if ($login['result'] != 'success') {
  header('HTTP/1.0 403 Forbidden');
  return;
}

if ($login['contactid']) {
  $user = Capsule::table('tblcontacts')->selectRaw("CONCAT(firstname, ' ', lastname) as full_name, email, permissions")->where("id", $login['contactid'])->first();
  $permissions = explode(',',$user->permissions);
  if (!in_array('tickets',$permissions)) {
    header('HTTP/1.0 403 Forbidden');
    return;
  }
} else {
  $user = Capsule::table('tblclients')->selectRaw("CONCAT(firstname, ' ', lastname) as full_name, email")->where("id", $login["userid"])->first();
}

echo json_encode(array('name' => $user->full_name, 'email' => $user->email, 'reference' => $user->email));