<?php

/*
  ---- notifications/notificationForm.php ----

  used to create notifications for an outreach

  - Contents -
  notificationForm() - form to choose timing, recipient and content of a custom notification
*/   

function notificationForm()
{
  $params = drupal_get_query_parameters();

  if (isset($params['OID'])){
    $OID = $params['OID'];
  } else {
    drupal_set_message('No outreach specified.', 'error');
  }

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Add Info: Notification'),
			);

  $form['fields']['tableHeader']=array(
				       '#markup'=>'<table>'
				       );
  
  $outreachName = dbGetOutreachName($OID);

  if (!$outreachName){
    drupal_set_message('Invalid outreach event.', 'error');
    return;
  }

  // simply displays the chosen outreach event
  $form['fields']['outreachName']=array(
				  '#prefix'=>'<tr><td>',
				  '#type'=>'textfield',
				  '#title'=>t('Outreach Name:'),
				  '#disabled'=>true,
				  '#default_value'=>$outreachName,
				  '#suffix'=>'</td></tr>',
				  );

  // allow the user to choose who the notification will go to (of those signed up or who own the outreach)
  $UIDs = dbGetUIDsForOutreach($OID);

  if(!empty($UIDs)){
    $form['fields']['UID'] = array(
				   '#prefix'=>'<tr><td>',
				   '#title'=>'Select Users',
				   '#type'=>'checkboxes',
				   '#options'=>$UIDs,
				   '#suffix'=>'</td>'
				   );
  } else {
    drupal_set_message('There are no users associated with this event.', 'error');
    drupal_goto('manageNotifications', array('query'=>array('OID'=>$OID)));
  }

  // allow the user to choose when the notification will arrive
  $form['fields']['dateHeader'] = array('#markup'=>'<td colspan="2" align="left" style="text-align:left">');

  $form['fields']['dateTargeted'] = array(
						   '#type' => 'date_popup', 
						   '#title' => t('Delivery Date:'),
						   '#date_format' => TIME_FORMAT,
						   '#date_label_position' => 'within', 
						   '#date_increment' => 1,
						   '#date_year_range' => '-20:+20',
						   '#datepicker_options' => array()
						   );

  $form['fields']['dateFooter'] = array('#markup'=>'</td>');
  
  $form['fields']['message']=array(
				   '#prefix'=>'<tr><td colspan="2">',
				   '#type'=>'textarea',
				   '#title'=>t('Message:'),
				   '#suffix'=>'</td></tr>'
				   );

  $form['fields']['submit']=array(
			'#prefix'=>'<tr><td colspan="2" style="text-align:right">',
			'#type'=>'submit',
			'#value'=>t('Submit'),
			'#suffix'=>'</td></tr>'
			);

  $form['fields']['tableFooter']=array(
				       '#markup'=>'</table>'
				       );

  return $form;
}

function notificationForm_validate($form, $form_state)
{
  $noneChecked = true;
  foreach($form_state['values']['UID'] as $UID){
    if ($UID != 0){
      $noneChecked = false;
    }
  }

  if($noneChecked){
    form_set_error("fields][UID", 'Please select a user.');
  }
  
  if($form_state['values']['dateTargeted'] == null){
    form_set_error("fields][dateTargeted", 'Please select a date.');
  }
}

function notificationForm_submit($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

  // generate the notification
  $notification = getFields(array('dateTargeted', 'message'), $form_state['values']);
  $notification = stripTags($notification); // allow some tags
  $notification['dateTargeted'] = dbDatePHP2SQL(strtotime($notification['dateTargeted']));
  $notification['bttnTitle'] = 'View Outreach';
  $notification['bttnLink'] = '?q=viewOutreach&OID='.$OID;
  $notification['OID'] = $OID;
  $notification['TID'] = dbGetTeamForOutreach($OID);
  $notification['dateCreated'] = dbDatePHP2SQL(time());
  foreach($form_state['values']['UID'] as $UID){
    if ($UID != null){
      $notification['UID'] = $UID;
      $result = dbAddNotification($notification);
    }
  }
  if ($result){
    drupal_set_message('Notification added!');
    drupal_goto('manageNotifications', array('query'=>array('OID'=>$OID)));
  } else {
    drupal_set_message('There was an error.', 'error');
  }
}

?>