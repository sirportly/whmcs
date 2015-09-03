<?PHP

function _doSirportlyAPICall($method, $postfields=array())
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
  $return  = json_decode($result, true);

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
  print_r($custom_fields);
  foreach ($custom_fields as $custom_field) {

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
          $html .= "<div class='radio'><label><input type='radio' name='customfield[" . $custom_field['system_name'] . "]' value='" . $option . "'>" . $option . "</label></div>";
        }
      break;

      case 'checkbox':
        $options = preg_split ('/$\R?^/m', $custom_field['values']);
        foreach ($options as $option) {
          $option = preg_replace("/[^0-9]/", "", $option);
          $selected = $option == $value ? 'checked=checked' : '';
          $html .= "<div class='checkbox'><label><input type='checkbox' name='customfield[" . $custom_field['system_name'] . "][]' value='" . $option . "'>" . $option . "</label></div>";
        }
      break;
    }
    $returnArray[] = array('name' => $custom_field['name'], 'description' => $custom_field['description'], 'input' => $html);
  }
  return $returnArray;
}

function sirportlyContacts($uid)
{
  $result = select_query('sirportly_contacts', 'sirportly_id', array('user_id' => $uid));
  $sirportly_ids = array();
  while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    $sirportly_ids[] = $row[0];
  }
  return array_values(array_unique($sirportly_ids));
}

function sirportlyTickets()
{
  ## Include the configuration
  include("config.php");

  ## Start the base query
  $query = "SELECT tickets.id, tickets.reference, tickets.subject, tickets.last_update_posted_at, department.name, status.colour, status.name, status.status_type FROM tickets WHERE contact.id = '18'";

  ## Run the query
  $result = _doSirportlyAPICall('tickets/spql', array('spql' => $query));

  ## Return the result
  return $result;
}

function sirportlyStoreContact($uid, $cid, $sirportly_id)
{
  insert_query('sirportly_contacts',
    array(
      'user_id'      => ($uid === null) ? 'NULL' : $uid,
      'contact_id'   => ($cid === null) ? 'NULL' : $cid,
      'sirportly_id' => $sirportly_id,
    )
  );
}

function sirportlyContact($uid, $cid)
{
  $response = get_query_val('sirportly_contacts', 'sirportly_id', array('user_id' => $uid));
  return $response;
}

function formatSirportlyErrors($errors=array()) {
  $output = '';
  foreach ($errors as $key => $value) {
    $function = is_array($value) ? __FUNCTION__ : 'htmlspecialchars';
    $key = preg_replace("/_id$/", "", ucfirst($key) );
    $output .= '<li>' . $key . ' ' . $function($value) . '</li>';
  }
  return $output;
}

function sirportly_departments()
{
  include("config.php");
  $returnArray = array();
  $departments = _doSirportlyAPICall('objects/departments');
  foreach ($departments as $key => $value) {
    $returnArray[] = array('id' => $value['id'], 'name' => $value['name']);
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
