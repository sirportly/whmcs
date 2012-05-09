<?PHP
function sirportly_api($action,$token,$secret,$postfields=array()){
  $settings = sirportly_settings();
  $ssl = ($settings['ssl'] == 'on' ? 'https://' : 'http://');
  $url = $ssl.$settings['url'];
  
  $query_string = "";
  foreach ($postfields AS $k=>$v) $query_string .= "$k=".urlencode($v)."&";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url.$action);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-Auth-Token: '.$token,'X-Auth-Secret: '.$secret));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $jsondata = curl_exec($ch);
  if (curl_error($ch))
  {
    global $smarty;
    $true = true;
    $smarty->assign_by_ref('sirportly_error', $true);
  } 
  
  curl_close($ch);
  $arr = json_decode($jsondata,true);
  
  if ($arr['error']) {
    global $smarty;
    $true = true;
    $smarty->assign_by_ref('sirportly_error', $true);
  }
    
     
    return $arr;
  
  
}

function sirportly_admin($action,$token,$secret,$postfields=array()){
  $settings = sirportly_settings();
  $ssl = ($settings['ssl'] == 'on' ? 'https://' : 'http://');
  $url = $ssl.$settings['url'];
  
  $curl = curl_init();	

	$header = array('X-Auth-Token: '.$token, 'X-Auth-Secret: '.$secret);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_URL, $url.$action);
	curl_setopt($curl, CURLOPT_BUFFERSIZE, 131072);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);

	$result = curl_exec($curl);
	curl_close($curl);
	return json_decode($result, true);
  
}


function sirportly_brands($token,$secret){
  $brand_array = array();
  $brand_array[0] = 'Disabled';
  $brands = sirportly_api('/api/v1/objects/brands',$token,$secret);

  foreach ($brands as $key => $value) {
    $brand_array[$value['id']] = $value['name'];
  }
  return $brand_array;
}

function sirportly_reply_to_ticket($token,$secret,$ticket_reference,$reply,$customer){
   
  $reply_result = sirportly_api('/api/v1/tickets/post_update',$token,$secret,array('ticket' => $ticket_reference, 'message' => $reply, 'customer' => $customer));
  return $reply_result;
}

function sirportly_view_ticket($token,$secret,$reference){
  $ticket = sirportly_api('/api/v1/tickets/ticket',$token,$secret,array('reference' => $reference));
  return $ticket;
}

function sirportly_status($token,$secret){
  $status_array = array();
  $status = sirportly_api('/api/v1/objects/statuses',$token,$secret);

  foreach ($status as $key => $value) {
    $status_array[$value['id']] = $value['name'];
  }
  return $status_array;
}

function sirportly_customer_id($id){
	$sirportly_customer = select_query('sirportly_customers', '', array('customerid' => $id));
	if (!mysql_num_rows($sirportly_customer)) {
		mysql_query("INSERT INTO `sirportly_customers` (`userid`, `customerid`) VALUES ('".$_SESSION['uid']."', '".$id."');");
	}
}

function sirportly_priorities($token,$secret){
  $priority_array = array();
  $priority = sirportly_api('/api/v1/objects/priorities',$token,$secret);

  foreach ($priority as $key => $value) {
    $priority_array[] = array('id' => $value['id'], 'name' => $value['name']);
  }
  return array_reverse($priority_array);
}

function sirportly_enabled()
{
  $sirportly_settings = sirportly_settings();
  return ($sirportly_settings['brand'] ? true : false);
}

function sirportly_clientarea_departments($token,$secret,$brand){
  $department_array = array();
  $departments = sirportly_api('/api/v1/objects/departments',$token,$secret,array('brand' => $brand));
  foreach ($departments as $key => $value) {
    if (!$value['private']) {
      $department_array[] = array('id' => $value['id'], 'name' => $value['name']);
    }
  }
  return $department_array;
}

function sirportly_submit_ticket($token,$secret,$params){
  $ticket = sirportly_api('/api/v1/tickets/submit',$token,$secret,$params);
  return $ticket;
}

function sirportly_clientarea_tickets($token,$secret,$customer,$brand=''){
  $ticket_array = array();
  $tickets = sirportly_spql($token,$secret,"SELECT tickets.id, tickets.reference, tickets.subject, tickets.submitted_at, tickets.last_update_posted_at, priorities.name, priorities.colour, departments.name FROM tickets WHERE brands.id = '".$brand."' AND customers.id = '".$customer."' ORDER BY tickets.last_update_posted_at DESC");
 
  foreach ($tickets['results'] as $key => $value) {

    $ticket_array[] = array(
      'id' => $value['0'],
      'tid' => $value['1'],
      'c' => $value['0'],
      'date' => fromMySQLDate($value['3'],'time'),
      'department' => $value['7'],
      'subject' => $value['2'],
      'status' => '<span style="color:#'.$value['6'].'">'.$value['5'].'</span>',
      'urgengy' => $value['7'],
      'lastreply' => ($value['4'] ? fromMySQLDate($value['4'], 'time') : 'Never'),
      'unread' => 1,
    );
  }
  return $ticket_array;
}

function sirportly_open_tickets($token,$secret,$customer,$brand)
{
  $ticket_array = array();
  $tickets = sirportly_api('/api/v1/tickets/spql',$token,$secret,array('spql' => "SELECT tickets.reference, tickets.id, tickets.subject, statuses.name, departments.name, tickets.last_update_posted_at, tickets.submitted_at, priorities.name FROM tickets WHERE tickets.status_type != '1' AND customers.id = '".$customer."' AND brands.id = '".$brand."'"));
  foreach ($tickets['results'] as $key => $ticket) {
    $created_at = fromMySQLDate($ticket['6'],'time');
    $last_updated = ($ticket['5'] ? fromMySQLDate($ticket['5'],'time') : 'Never'); 
    $ticket_array[] = array(
      'date'       => $created_at, 
      'tid'        => $ticket['0'], 
      'subject'    => $ticket['2'], 
      'c'          => $ticket['1'],
      'lastreply' => $last_updated, 
      'department' => $ticket['4'], 
      'status'     => $ticket['3'],
			'priority'     => $ticket['7']);
  }  
  return $ticket_array;  
}

function sirportly_spql($token,$secret,$query)
{
  return sirportly_api('/api/v1/tickets/spql',$token,$secret,array('spql' => $query));
}

function sirportly_settings()
{
  $result = select_query('tbladdonmodules', '', array('module' => 'sirportly'));
  $settings = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
   $settings[$row['setting']] = $row['value'];
  }
  return $settings;
}