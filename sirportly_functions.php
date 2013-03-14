<?PHP

function sirportly_brands($token,$secret){
  $brand_array = array();
  $brand_array[0] = 'Disabled';
  $brands = curl('/api/v1/objects/brands');

  foreach ($brands['results'] as $key => $value) {
    $brand_array[$value['id']] = $value['name'];
  }
  return $brand_array;
}

function sirportly_status($token,$secret){
  $status_array = array();
  $statuses = curl('/api/v1/objects/statuses');

  foreach ($statuses['results'] as $key => $value) {
    $status_array[$value['id']] = $value['name'];
  }
  return $status_array;
}

function sirportly_priorities($token,$secret){
  $priority_array = array();
  $priorities = curl('/api/v1/objects/priorities');
  
  foreach ($priorities['results'] as $key => $value) {
    $priority_array[] = array('id' => $value['id'], 'name' => $value['name']);
  }
  return $priority_array;
}

function sirportly_enabled()
{
  $sirportly_settings = sirportly_settings();
  return ($sirportly_settings['brand'] ? true : false);
}

function sirportly_ticket_table($results)
{
  foreach ($results as $key => $ticket) {
    $tickets[] = array(
      'date'       => fromMySQLDate($ticket['submitted_at'], time),
      'tid'        => $ticket['reference'],
      'subject'    => $ticket['subject'],
      'c'          => $ticket['id'],
      'lastreply'  => fromMySQLDate($ticket['last_update_posted_at'], time),
      'department' => $ticket['department']['name'],
      'status'     => "<span style='color:#{$ticket['status']['colour']}'>{$ticket['status']['name']}</span>"
    );
  }
  return $tickets;
}

function sirportly_contact()
{
  $sirportly_customer = select_query('sirportly_customers', 'customerid', array('userid' => $_SESSION['uid']) );
  if ( mysql_num_rows($sirportly_customer) ) {
    $cid  = mysql_fetch_array($sirportly_customer, MYSQL_ASSOC);
    return $cid['customerid'];
  } else {
    return false;
  }
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

function curl($action, $params = array())
{
  $settings = sirportly_settings();
  $url = ($settings['ssl'] == 'on' ? 'https://' : 'http://').$settings['url'];  
  $curl = curl_init();	
  $default_params = array('brand' => $settings['brand']);
  $params = array_merge($default_params, $params);

	$header = array('X-Auth-Token: '.$settings['token'], 'X-Auth-Secret: '.$settings['secret']);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_URL, $url.$action);
	curl_setopt($curl, CURLOPT_BUFFERSIZE, 131072);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

	$result = curl_exec($curl);
	$status_code = curl_getinfo($curl);
	$json   = json_decode($result, true);
	
	curl_close($curl);
	logModuleCall("Sirportly", $action, $params, $result, $json);
	return array('status' => $status_code['http_code'], 'results' => $json);
}