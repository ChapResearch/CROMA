<?php

include_once("/var/www-croma/croma_modules/helperFunctions.inc");

function outreachForm()
{
  global $user;
  $params = drupal_get_query_parameters();
  $teams = dbGetTeamsForUser($user->uid);
  $TID = $teams[0]['TID']; // TODO - should handle having multiple teams

  $new = true;

  if(isset($params["OID"])){
    $data = dbGetOutreach($params["OID"]);
    $new = false;
  }

  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Enter outreach data below'),
    );

    $form['fields']['name']=array(
        '#prefix'=>'<table><tr><td colspan="6" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('Outreach Name:'),
	'#default_value'=>$new?'':$data['name'],
	'#suffix'=>'</td></tr>'
      );

    //TO-DO: Fix the storing of start time and end time

   $form['fields']['startTime']=array(
        '#prefix'=>'<tr><td colspan="3"  style="text-align:center">',
        '#type'=>'date',
        '#title'=>t('Start Time:'),
	//	'#default_value'=>$new?'':$data['startTime'],
	'#suffix'=>'</td>'
      );

   $form['fields']['endTime']=array(
        '#prefix'=>'<td colspan="3"  style="text-align:center">',
        '#type'=>'date',
        '#title'=>t('End Time:'),
	//	'#default_value'=>$new?'':$data['endTime'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['description']=array(
        '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
        '#type'=>'textarea',
        '#title'=>t('Description:'),
	'#default_value'=>$new?'':$data['description'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['peopleImpacted']=array(
        '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('Number of People Impacted:'),
	'#default_value'=>$new?NULL:$data['peopleImpacted'],
	'#suffix'=>'</td>'
      );

    $form['fields']['totalAttendance']=array(
        '#prefix'=>'<td colspan="3" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('Total Attendance:'),
	'#default_value'=>$new?NULL:$data['totalAttendance'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['type']=array(
        '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('Type:'),
	'#default_value'=>$new?'':$data['type'],
	'#suffix'=>'</td>'
      );

    $form['fields']['status']=array(
        '#prefix'=>'<td colspan="3" style="text-align:center">',
        '#type'=>'radios',
	'#default_value'=>'isOutreach',
	'#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
        '#title'=>t('Status:'),
	'#default_value'=>$new?'':$data['status'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['address']=array(
        '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
	'#type'=>'textfield',
        '#title'=>t('Address:'),
	'#default_value'=>$new?'':$data['address'],
	'#suffix'=>'</td>'
      );

    $form['fields']['state']=array(
        '#prefix'=>'<td colspan="2" style="text-align:center">',
        '#type'=>'select',
        '#title'=>t('State:'),
        '#options'=>states_list(),
	'#default_value'=>$new?'Other':$data['state'],
	'#suffix'=>'</td>'
      );

    $form['fields']['country']=array(
        '#prefix'=>'<td colspan="2" style="text-align:center">',
	'#type'=>'select',
        '#title'=>t('Country:'),
        '#options'=>countries_list(),
	'#default_value'=>$new?'United States':$data['country'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['co_organization']=array(
      '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
      '#type'=>'textfield',
      '#title'=>t("Host Organization:"),
      '#default_value'=>$new?'':$data['co_organization'],
      '#suffix'=>'</td>'
      ); 

    $form['fields']['co_firstName']=array(
       '#prefix'=>'<td colspan="2" style="text-align:center">',
       '#type'=>'textfield',
       '#title'=>t("Host Contact's First Name:"),
       '#default_value'=>$new?'':$data['co_firstName'],
       '#suffix'=>'</td>'
      );

    $form['fields']['co_lastName']=array(
      '#prefix'=>'<td colspan="2" style="text-align:center">',
      '#type'=>'textfield',
      '#title'=>t("Host Contact's Last Name:"),
      '#default_value'=>$new?'':$data['co_lastName'],
      '#suffix'=>'</td></tr>'
      );

    $form['fields']['co_phoneNumber']=array(
     '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
     '#type'=>'textfield',
     '#title'=>t("Host Contact's Phone Number:"),
     '#default_value'=>$new?'':$data['co_phoneNumber'],
     '#suffix'=>'</td>'
     );

     $form['fields']['co_email']=array(
     '#prefix'=>'<td colspan="3" style="text-align:center">',
     '#type'=>'textfield',
     '#title'=>t("Host Contact's Email:"),
     '#default_value'=>$new?'':$data['co_email'],
     '#suffix'=>'</td></tr>'
      );

     $form['fields']['testimonial']=array(
     '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
     '#type'=>'textarea',
     '#title'=>t('Testimonial:'),
     '#default_value'=>$new?'':$data['testimonial'],
     '#suffix'=>'</td></tr>'
      );


     $form['fields']['thumbnail']=array(
	  '#markup'=>'<td colspan="4" style="text-align:center">'
     );
     
     $form['fields']['FID']=array(
     '#title'=>t('Thumbnail Picture for Outreach:'),
     '#type'=>'managed_file',
     '#upload_location' => 'public://',
     '#upload_validators' => array(
	       'file_validate_extensions' => array('gif png jpg jpeg'),
	       'file_validate_size' => array(50000*1024),         // 500k limit currently
      ),    
     '#default_value'=>$new?'':$data['FID'],
     '#size' => 48,
      );
 

    $form['fields']['cancelled']=array(
       '#prefix'=>'<td colspan="1" style="text-align:center"><a><button>Cancel</button></a>',
       '#title'=>t('Cancel'),
       '#default_value'=>$new?'':$data['cancelled'],
       '#suffix'=>'</td></tr></table>'
      );

    $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit')
	);
    
    return $form;
}

function outreachForm_validate($form, $form_state)
{
  if(empty($form_state['values']['name'])){
     form_set_error('name','Name cannot be empty');
  }

  if(!empty($form_state['values']['peopleImpacted']) &&
     !is_numeric($form_state['values']['peopleImpacted'])){
    form_set_error('peopleImpacted','Must be a number');
  }

  if(!empty($form_state['values']['peopleReached']) &&
     !is_numeric($form_state['values']['peopleReached'])){
    form_set_error('peopleReached','Must be a number');
  }

  if(!empty($form_state['values']['description'])) {
    if(strlen($form_state['values']['description'])>999){
      form_set_error('description',"The description must be fewer than 1000 characters");
    }
  }
  
  if(empty($form_state['values']['status'])){
    form_set_error('status','Please specify a status');
  }

  if(!empty($form_state['values']['co_phoneNumber'])) {
    if(!is_numeric($form_state['values']['co_phoneNumber'])) {
      form_set_error('co_phoneNumber','That is not a valid phone number!');
    }
  }
}

function outreachForm_submit($form, $form_state)
{
  global $user;
  $UID = $user->uid;
  $teams = dbGetTeamsForUser($user->uid);
  $TID = $teams[0]['TID']; // TODO - should handle having multiple teams

  $params = drupal_get_query_parameters();

  $fields = array("name", "peopleImpacted", "peopleReached", "description", "address", "state", "country", "type", "status", "cancelled", "FID", "testimonial");
  $outreachData = getFields($fields, $form_state['values']);
  $outreachData["TID"] = $TID;

  if ($outreachData['FID'] != null){
    $f = file_load($form_state['values']['FID']);
    $f->status = FILE_STATUS_PERMANENT;
    file_save($f);
    file_usage_add($f, 'CROMA - outreach', 'pictures', $f->fid); // tells Drupal we're using the file
  }

  if (isset($params["OID"])){ // updating existing event
    $result = dbUpdateOutreach($params["OID"], $outreachData);
    if ($result){
      drupal_set_message("Outreach updated!");
    } else {
      drupal_set_message("Outreach not updated!");
    }
    // TODO - update times
  } else { // adding new event
    $outreachData['logDate'] = dbDatePHP2SQL(time());
    $OID = dbCreateOutreach($outreachData);
    if ($OID != false){
      dbAddUserAsOwnerOfOutreach($UID, $OID);
      $timeFields = array("startTime", "endTime");
      $timeData = getFields($timeFields, $form_state['values']);
      $timeData['OID'] = $OID;
      //dbCreateTimesVsOutreach($timeData);
      drupal_set_message("Outreach created!");
    } else {
      form_set_error("Outreach not created successfully");
    }
  }
  drupal_goto("viewTeamOutreach", array('query'=>array("TID"=>$TID)));
}

?>