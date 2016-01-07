<?PHP

function _doSirportlyAPICall($method, $postfields=array(), $jsonDecode=true)
{
  ## Include the configuration
  include("config.php");

  ## Tidy up the URL
  $apiUrl  = rtrim($baseUrl, '/') . "/api/v2/";

  ## Specify default params
  $default_params = array('brand' => $BrandId);

  ## Merge the postfields
  $postfields = array_merge($default_params, $postfields);

  ## Set the required headers
  $headers = array('X-Auth-Token: ' . $apiToken, 'X-Auth-Secret: ' . $apiSecret);

  ## Make the API request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiUrl . $method);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec ($ch);

  ## Decode the response
  if ($jsonDecode) {
    $return  = json_decode($result, true);
  } else {
    $return  = $result;
  }

  ## Log the API call
  logModuleCall('Sirportly', $method, $postfields, $result, $return);

  ## Return the decoded response
  return $return;
}

function sirportlyCustomFields($department, $params)
{
  include("config.php");
  $returnArray = array();
  $custom_fields = _doSirportlyAPICall('objects/custom_fields', array('department' => $department));
  foreach ($custom_fields as $custom_field) {

    ## Don't show private fields
    if ($custom_field['private']) {
      continue;
    }

    ## Reset the HTML for each field
    $html = "";

    ## Get the system name from Sirportly
    $system_name = $custom_field['system_name'];

    ## Get the default value from Sirportly
    $default_value = $custom_field['default_value'];

    ## Determine the value
    $value = isset($params[$system_name]) ? $params[$system_name] : $default_value;

    switch ($custom_field['field_type']) {
      case 'string':
        $html  = "<input type='text' name='customfield[" . $custom_field['system_name'] . "]' value='" . $value . "' size='30' class='form-control'>";
      break;

      case 'text':
        $html  = "<textarea name='customfield[" . $custom_field['system_name'] . "]'rows='3' style='width:100%;' class='form-control'>" . $value . "</textarea>";
      break;

      case 'select':
        $html = "<select name='customfield[" . $custom_field['system_name'] . "]' class='form-control'>";
        $select_options = preg_split ('/$\R?^/m', $custom_field['values']);
        foreach ($select_options as $option) {
          $option = preg_replace("/[^0-9]/", "", $option);
          $selected = $option == $value ? 'selected=selected' : '';
          $html .= "<option value='" . $option . "' " . $selected . ">" . $option . "</option>";
        }
        $html .= "</select>";
      break;

      case 'password':
        $html  = "<input type='password' name='customfield[" . $custom_field['system_name'] . "]' value='" . $value . "' size='30' class='form-control'>";
      break;

      case 'radio':
        $options = preg_split ('/$\R?^/m', $custom_field['values']);
        foreach ($options as $option) {
          $option = preg_replace("/[^0-9]/", "", $option);
          $selected = $option == $value ? 'checked=checked' : '';
          $html .= "<div class='radio'><label><input type='radio' name='customfield[" . $custom_field['system_name'] . "]' value='" . $option . "' " . $selected . ">" . $option . "</label></div>";
        }
      break;

      case 'checkbox':
        $checked = $option == $value ? 'checked=checked' : '';
        $html .= "<div class='checkbox'><label><input type='hidden' name='customfield[" . $custom_field['system_name'] . "]' value='0'><input type='checkbox' name='customfield[" . $custom_field['system_name'] . "]' value='1'></label></div>";
      break;
    }
    $returnArray[] = array('name' => $custom_field['name'], 'description' => $custom_field['description'], 'input' => $html);
  }
  return $returnArray;
}

function locateSirportlyUpdateAuthor($sirportlyContactID)
{
  $result = get_query_vals('sirportly_contacts', '*', array('sirportly_id' => $sirportlyContactID), "",  'sirportly_id', 1);
  return array('contact_id' => $result['contact_id'], 'user_id' => $result['user_id']);
}

function sirportlyContacts($uid, $cid)
{

  ## Include the configuration
  include("config.php");

  if ($canOnlyViewOwnTickets) {
    $return[] = findOrCreateSirportlyContact($uid, $cid);
  } else {
    $result = select_query('sirportly_contacts', 'sirportly_id', array('user_id' => $uid));
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $return[] = $row[sirportly_id];
    }
  }

  return $return;
}

function sirportlyTickets($contacts)
{
  ## Include the configuration
  include("config.php");

  ## Start the query
  $query = "SELECT tickets.id, tickets.reference, tickets.subject, tickets.last_update_posted_at, department.name, status.colour, status.name, status.status_type FROM tickets WHERE brand.id = {$BrandId} AND (";

  foreach ($contacts as $contact) {
    $criteria[] = "contact.id" . " = '" . $contact . "'";
  }
  $query .= implode(" OR ", $criteria) . ")";

  ## Order the tickets by the last update posted at time
  $query .= " ORDER BY tickets.last_update_posted_at DESC";

  ## Run the query
  $result = _doSirportlyAPICall('tickets/spql', array('spql' => $query));

  ## Return the result
  return $result;
}

function storeSirportlyContact($uid, $cid, $sirportly_id)
{
  insert_query('sirportly_contacts',
    array(
      'user_id'      => ($uid === null) ? 'NULL' : $uid,
      'contact_id'   => ($cid === null) ? 'NULL' : $cid,
      'sirportly_id' => $sirportly_id,
    )
  );
}

function findOrCreateSirportlyContact($uid, $cid)
{
  ## Setup the query
  $user_query   = ($uid === null) ? 'is NULL' : "= '{$uid}'";
  $client_query = ($cid === null) ? 'is NULL' : "= '{$cid}'";

  $query = full_query("SELECT `sirportly_id` FROM `sirportly_contacts` WHERE `user_id` {$user_query} AND `contact_id` {$client_query}");
  $result = mysql_fetch_array($query, MYSQL_ASSOC);

  if (empty($result['sirportly_id'])) {

    ## Fetch the client details
    $clientDetails = getClientsDetails($uid, $cid);

    ## Attempt to search Sirportly for the contact
    $contactSearch = _doSirportlyAPICall('contacts/search', array(
      'query' => $clientDetails['email'],
      'types' => 'email',
      'limit' => '1'
    ));

    ## Check to see if we encountered any errors
    if ( checkForSirportlyErrors($contactSearch) ){
      die('Unable to create Sirportly contact');
    }

    if (empty($contactSearch)) {
      ## Attempt to create the contact
      $createSirportlyContact = _doSirportlyAPICall('contacts/create', array(
        'name'    => $clientDetails['fullname'],
        'company' => $clientDetails['company']
      ));

      ## Check to see if we encountered any errors
      if ( checkForSirportlyErrors($createSirportlyContact) ){
        die('Unable to create Sirportly contact');
      }

      ## Attempt to create the contact method
      $createSirportlyContactMethod = _doSirportlyAPICall('contacts/add_contact_method', array(
        'contact'     => $createSirportlyContact['id'],
        'method_type' => 'email',
        'data'        => $clientDetails['email']
      ));

      ## Check to see if we encountered any errors
      if ( checkForSirportlyErrors($createSirportlyContactMethod) ) {
        die('Unable to create Sirportly contact method');
      }

      ## Store the Sirportly contact ID for future
      storeSirportlyContact($uid, $cid, $createSirportlyContact['id']);

      ## Return the contact id
      return $createSirportlyContact['id'];
    } else {
      ## Store the Sirportly contact ID for future
      storeSirportlyContact($uid, $cid, $contactSearch['0']['contact']['id']);

      ## Return the contact id
      return $contactSearch['0']['contact']['id'];
    }

    ## If we got here something seriously went wrong
    die('Contact doesn\'t exist');
  } else {
    ## Return the contact id
    return $result['sirportly_id'];
  }
}

function checkForSirportlyErrors($output)
{
  if (array_key_exists('error', $output) || array_key_exists('errors', $output)){
    return true;
  } else {
    return false;
  }
}

function formatSirportlyErrors($errors=array()) {
  $output = '';
  foreach ($errors as $key => $value) {
    $output .= "<li>".preg_replace("/_id$/", "", ucfirst($key) )." ".$value['0']."</li>";
  }
  return $output;
}

function sirportlyDepartments()
{
  include("config.php");
  $returnArray = array();
  $departments = _doSirportlyAPICall('objects/departments');
  foreach ($departments as $department) {
    if (!$department['private']) {
      $returnArray[] = array('id' => $department['id'], 'name' => $department['name']);
    }
  }
  return $returnArray;
}

function sirportly_priorities(){
  include("config.php");
  $returnArray = array();
  $priorities  = _doSirportlyAPICall('objects/priorities');
  foreach ($priorities as $key => $value) {
    $returnArray[] = array('id' => $value['id'], 'name' => $value['name']);
  }
  return $returnArray;
}

function sirportly_customfields(){
  include("config.php");
  $returnArray  = array();
  $customFields = _doSirportlyAPICall('objects/custom_fields');
  foreach ($customFields as $key => $value) {
    $returnArray[] = array('id' => $value['id'], 'name' => $value['name']);
  }
  return $returnArray;
}

function sirportly_upload_attachments($attachments) {
  include("config.php");
  $returnArray  = array();
  $attachments = rearray_uploaded_files($attachments);
  foreach ($attachments as $attachment) {
    $params = array('file' => '@' . $attachment['tmp_name'], 'filename' => $attachment['name']);
    $sirportlyAttachment = _doSirportlyAPICall('tickets/add_attachment', $params);
    $returnArray[] = $sirportlyAttachment['temporary_token'];
  }
  return $returnArray;
}

function rearray_uploaded_files(&$file_post) {
  $file_ary = array();
  $file_count = count($file_post['name']);
  $file_keys = array_keys($file_post);

  for ($i=0; $i<$file_count; $i++) {
    foreach ($file_keys as $key) {
        $file_ary[$i][$key] = $file_post[$key][$i];
    }
  }

  return $file_ary;
}

?>
