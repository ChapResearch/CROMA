<?php

/*
  ---- outreach/writeUpForm.php ----

  used to allow users to create/edit an outreach's write up

  - Contents -
  writeUpForm() - creates the form for write up data
  save() - saves the write up without sending out to moderator for approval or being approved
  approve() - approves the write up and sets event to locked as well as sending out a notification
  reject() - rejects a write up that was in approval stage and also sends out a notifications
  backToEvent1() - takes you back to page you came from
*/

function writeUpForm($form, &$form_state){
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  $new = $form_state['new'] = true;
  $approving = $form_state['approving'] = false;
  $approved = $form_state['approved'] = false;  
  $editor = $form_state['editor'] = null;

  if (isset($params["OID"])){
    $form_state['OID'] = $params['OID'];
    $outreach = dbGetOutreach($params["OID"]);
    $new = $form_state['new'] = false;
  } else {
    drupal_set_message('There is no outreach write up selected.', 'error');
    return;
  }

  // load in the name of the editor of the write-up
  if (!empty($outreach['writeUpUID'])){
    $editor = $form_state['editor'] = dbGetUserName($outreach['writeUpUID']);
  }
  
  // if the write-up has been submitted, then the current user is in the process of approving the write-up
  if (isset($params["approving"]) || $outreach['isWriteUpSubmitted']){
    $approving = $form_state['approving'] = true;  
  }
  
  // if the write-up has already been approved, set variables appropriately
  if (isset($params["approved"]) || $outreach['isWriteUpApproved']){
    $approved = $form_state['approved'] = true;  
  }
  
  $outreachName = $outreach['name'];
  
  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Add Write-Up: ' . '<b>' . $outreachName . '</b>'),
			);
  
  if (!$new){
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToEvent1'),
				  '#type' => 'submit',
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if (!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }

  $form['fields']['markupOne']=array('#markup'=>'<table>');
  
  $form['fields']['writer']=array(
				  '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
				  '#type'=>'textfield',
				  '#title'=>t('Written By:'),
				  '#disabled'=>true,
				  '#default_value'=>empty($editor)? dbGetUserName($UID): $editor,
				  '#suffix'=>'</td></tr>'
				  );

  $form['fields']['writeUp']=array(
				   '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
				   '#type'=>'textarea',
				   '#title'=>t('Write-Up:'),
				   '#default_value'=>$new?'':$outreach['writeUp'],
				   '#placeholder'=>'Maximum of 1000 characters',
				   '#suffix'=>'</td></tr>',
				   '#disabled'=>$approved,
				   '#maxlength_js'=>'TRUE',
				   '#maxlength'=>'1000'
				   );

  $form['fields']['totalAttendance']=array(
					   '#prefix'=>'<td colspan="3" style="text-align:center">',
					   '#type'=>'textfield',
					   '#title'=>t('Total Event Attendance:'),
					   '#default_value'=>$new?NULL:$outreach['totalAttendance'],
					   '#placeholder'=>'Total people that attended the event (i.e. 2000)',
					   '#disabled'=>$approved,
					   '#suffix'=>'</td></tr>'
					   );

  $form['fields']['testimonial']=array(
				       '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
				       '#type'=>'textarea',
				       '#title'=>t('Comments/Testimonials:'),
				       '#default_value'=>$new?'':$outreach['testimonial'],
				       '#placeholder'=>'Maximum of 500 characters',
				       '#disabled'=>$approved,
				       '#suffix'=>'</td></tr>',
				       '#maxlength_js'=>'TRUE',
				       '#maxlength'=>'500'
				       );
  if (!$approved){
    $form['fields']['save']=array(
				  '#prefix'=>'<tr><td colspan="6" style="text-align:right">',
				  '#type' => 'submit',
				  '#submit'=>array('save'),
				  '#value'=>t('Save'),
				  '#suffix'=>''
				  );
  }

  if ($approving){
    $form['fields']['reject']=array(
				     '#prefix'=>'',
				     '#type' => 'submit',
				     '#submit'=>array('reject'),
				     '#value'=>t('Reject'),
				     '#suffix'=>''
				     );

    $form['fields']['approve']=array(
				     '#prefix'=>'',
				     '#type' => 'submit',
				     '#submit'=>array('approve'),
				     '#value'=>t('Approve'),
				     '#suffix'=>'</td></tr>'
				     );
  } else if (!$approved){
    $form['fields']['submit']=array(
				    '#prefix'=>'',
				    '#type' => 'submit',
				    '#value'=>t('Submit'),
				    '#sorted'=>false,
				    '#suffix'=>'</td></tr>'
				    );
  }



  $form['fields']['finalFooter']=array('#markup'=>'</table>');

  return $form;
}

function writeUpForm_validate($form, $form_state)
{
  // currently no validation to be done
}

function writeUpForm_submit($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];
  $OID = $params["OID"];

  $writeUpFields = array("totalAttendance", "testimonial", "writeUp");
  $writeUpData = getFields($writeUpFields, $form_state['values']);
  $writeUpData = stripTags($writeUpData, ''); // remove all tags
  if (empty($writeUpData["totalAttendance"])){
    $writeUpData["totalAttendance"] = null;
  }
  $writeUpData['writeUpUID'] = $user->uid;
  $writeUpData['isWriteUpSubmitted'] = true;

  $result = dbUpdateOutreach($OID, $writeUpData);

  // notify all other moderators for the team
  $notification = array();
  $userName = dbGetUserName($user->uid);
  $outName = dbGetOutreachName($OID);
  $notification['dateCreated'] = dbDatePHP2SQL(time());
  $notification['dateTargeted'] = dbDatePHP2SQL(time());
  $notification['message'] = "$userName has created a write up for  $outName.";
  $notification['bttnTitle'] = 'View';
  $notification['bttnLink'] = '?q=viewOutreach&OID=' . $OID;
  $notification['TID'] = $TID;
  notifyUsersByRole($notification, 'moderator');

  drupal_set_message("Write Up Submitted!");
  drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

function save($form, $form_state)
{
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];
  $OID = $params["OID"];

  $writeUpFields = array("totalAttendance", "testimonial", "writeUp");
  $writeUpData = getFields($writeUpFields, $form_state['values']);
  $writeUpData = stripTags($writeUpData, ''); // remove all tags
  if (empty($writeUpData["totalAttendance"])){
    $writeUpData["totalAttendance"] = null;
  }

  $result = dbUpdateOutreach($OID, $writeUpData);
  drupal_set_message("Write Up Saved!");
  drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

function approve($form, $form_state){

  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];
  $OID = $params["OID"];

  $writeUpFields = array("totalAttendance", "testimonial", "writeUp");
  $writeUpData = getFields($writeUpFields, $form_state['values']);
  $writeUpData = stripTags($writeUpData, ''); // remove all tags
  $writeUpData['isWriteUpApproved'] = true;
  $writeUpData['isWriteUpSubmitted'] = 0;
  $writeUpData['status'] = 'locked';
  if (empty($writeUpData["totalAttendance"])){
    $writeUpData["totalAttendance"] = null;
  }

  $result = dbUpdateOutreach($OID, $writeUpData);

  $notification = array(
			'TID' => $TID,
			'dateCreated' => dbDatePHP2SQL(time()),
			'dateTargeted' => dbDatePHP2SQL(time()),
			'message' => dbGetUserName($UID).' has just approved '.dbGetOutreachName($OID).'!',
			'bttnLink' => '?q=viewOutreach&OID='.$OID,
			'bttnTitle' => 'View Outreach'
			);
  
  // notify the appropriate people
  $outreachOwnerUID = dbGetOutreachOwner($OID);
  if ($UID != $outreachOwnerUID){
    dbAddNotification($notification);
  }
  notifyUsersByRole($notification, 'moderator');
  notifyUsersByRole($notification, 'teamAdmin');
  notifyUsersByRole($notification, 'teamOwner');

  drupal_set_message("Write up approved!");
  drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

function reject($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];
  $OID = $params["OID"];
  $outreachData = dbGetOutreach($OID);
  $writeUpUpdate['status'] = "doingWriteUp";
  $writeUpUpdate['writeUpUID'] = null;
  $writeUpUpdate['isWriteUpSubmitted'] = 0;

  $result = dbUpdateOutreach($OID, $writeUpUpdate);

  $notification = array();
  $userName = dbGetUserName($user->uid);
  $outName = dbGetOutreachName($OID);
  $notification['dateCreated'] = dbDatePHP2SQL(time());
  $notification['dateTargeted'] = dbDatePHP2SQL(time());
  $notification['message'] = "$userName has rejected a write up for $outName.";
  $notification['bttnTitle'] = 'Redo Write Up';
  $notification['bttnLink'] = '?q=viewOutreach&OID=' . $OID;
  $notification['TID'] = $TID;
  $notification['UID'] = $outreachData['writeUpUID'];

  dbAddNotification($notification);

  drupal_set_message("Write Up Rejected.");
  drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

function backToEvent1()
{
  $params = drupal_get_query_parameters(); //getting the parameters
  $OID = $params["OID"];

  drupal_goto("viewOutreach", array('query'=>array('OID'=>$OID)));
}
?>