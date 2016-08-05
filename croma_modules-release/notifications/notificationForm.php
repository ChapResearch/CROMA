<?php

function notificationForm()
{
  $params = drupal_get_query_parameters();

  if (isset($params['OID'])){
    $OID = $params['OID'];
  } else {
    drupal_set_message('Not outreach specified!', 'error');
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
    drupal_set_message('Invalid outreach event!', 'error');
    return;
  }

  $form['fields']['outreachName']=array(
				  '#prefix'=>'<tr><td>',
				  '#type'=>'textfield',
				  '#title'=>t('Outreach Name:'),
				  '#disabled'=>TRUE,
				  '#default_value'=>$outreachName,
				  '#suffix'=>'</td></tr>',
				  );

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
    drupal_set_message('There are no users associated with this event!', 'error');
    drupal_goto('manageNotifications', array('query'=>array('OID'=>$OID)));
    return;
  }

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
    form_set_error("fields][UID", 'Please select a user!');
  }
  
  if($form_state['values']['dateTargeted'] == null){
    form_set_error("fields][dateTargeted", 'Please select a date!');
  }
}

function notificationForm_submit($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

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
    drupal_set_message('There was an error!', 'error');
  }
}

// deleteNotification() - Deletes notification given by $NID. Used on the manageNotifications page.
function deleteNotification($NID, $OID)
{
  dbDeleteNotification($NID);
  drupal_set_message('Notification has been deleted!');

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('manageNotifications', array('query'=>array('OID'=>$OID)));
  }
}

?>