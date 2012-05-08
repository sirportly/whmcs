<?php
  # require whmcs functions 
  require("../../../dbconnect.php");
  require("../../../includes/functions.php");

  # find first administrator
  $administrator = select_query('tbladmins');
  $administrator = mysql_fetch_array($administrator, MYSQL_ASSOC);
  
  # try and process the login
  $login = localAPI('validatelogin',array('email' => $_REQUEST['username'], 'password2' => $_REQUEST['password']),$administrator['id']);

  # couldn't process the login, so forbid access
  if ($login['result'] != 'success') {
    header('HTTP/1.0 403 Forbidden');
    return;
  }
  
  # check to see if login was a client or a contact
  if ($login['contactid']) {
    $user = full_query("SELECT CONCAT(`firstname`, ' ', `lastname`), `permissions`, `email`  FROM `tblcontacts` WHERE `id` = '".$login['contactid']."'");
    $user = mysql_fetch_array($user, MYSQL_BOTH);
    $permissions = explode(',',$user['permissions']);
    if (!in_array('tickets',$permissions)) {
      header('HTTP/1.0 403 Forbidden');
      return;
    } 
  } else {
    $user = full_query("SELECT CONCAT(`firstname`, ' ', `lastname`), `email` FROM `tblclients` WHERE `id` = '".$login['userid']."'");
    $user = mysql_fetch_array($user, MYSQL_BOTH);    
  }
  
  # output the JSON
  echo json_encode(array('name' => $user['0'], 'email' => $user['email'], 'reference' => $user['email']));