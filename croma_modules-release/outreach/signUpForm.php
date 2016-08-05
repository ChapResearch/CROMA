<?php

/*
  ---- outreach/signUpForm.php ----

  used to allow users to sign up (make a commitment) for outreaches to attend or to cancel a commitment

  - Contents -
  signUp() - displays the form for a user to sign up for an outreach
  signUp_validate() - validates signUp form
  signUp_submit() - submit signUp form
  cancelCommit() - deletes the commitment that a user has made for that outreach (called by signUp() if cancel button clicked)
*/   

// signUp()- Displays the form for a user to sign up for an outreach

function signUp($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $new = $form_state['new'] = true;
  $params = drupal_get_query_parameters();

  // various checks for permissions
  if(dbGetTeamsForUser($user->uid) == false){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // getting the outreach ID from the URL parameters and setting that and the team ID into local variables
  if(isset($params['OID']) && $params['OID'] > 0){ 
    $OID = $params['OID'];
    $TID = dbGetTeamForOutreach($OID);

    if(!dbIsUserApprovedForTeam($UID, $TID)) {
      drupal_set_message('You do not have the permission to contribute to this event!', 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
    }
    if(dbIsOutreachOver($OID)){
      drupal_set_message('You can not contribute to this event! It has already ended.', 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
    }
    
    $outreach = dbGetOutreach($OID); 
    
    if(dbIsUserSignedUp($UID, $OID)){ // if user is already signed up for outreach then sets "new" to false
      $new = $form_state['new'] = false;
      $data = dbGetUserSignUpType($UID, $OID); // getting data related to sign up
    }

    $types = array('prep'=>'Preparation','atEvent'=>'At Event','writeUp'=>'Write Up','followUp'=>'Follow Up');

    $form = array();

    $form['fields']=array(
			  '#type'=>'fieldset',
			  '#title'=>t('Sign Up For Outreach: ' . dbGetOutreachName($OID)),
			  );

    // displays outreach owner's name and email (unless they are no longer on the team)
    $ownerUID = dbGetOutreachOwner($OID);
    $email = dbGetUserPrimaryEmail($ownerUID);
    if(dbGetUserName($ownerUID) != -1){
      $markup = '<b>Owner of Outreach: </b>' . dbGetUserName($ownerUID);
      if(dbIsUserApprovedForTeam($ownerUID, $TID)){
	$markup .= "<br><b>Email of Owner: </b><a href=\"mailto:$email\" target=\"_top\">$email</a>";
      } else {
	$markup .= ' (no longer on this team)<br>';
      }
    }
    else{
      $markup = '<b>Owner of Outreach: </b>[none]<br>';
      $markup .= '<b>Email of Owner: </b>[none]';
    }

    $form['tableHeader']=array(
			       '#markup' => $markup
			       );

    // signing up for time slots listed in the array above (local variable --> "types")
    $form['fields']['times']=array(
				   '#prefix'=>'<table colspan="4"><tr><td colspan="2">',
				   '#type'=>'checkboxes',
				   '#title'=>t('<h4>Which time(s) would you like to sign up for?</h4>'),
				   '#options'=> $types,
				   '#default_value'=>$new?array():$data,
				   '#suffix'=>'</td>',
				   );

    // obligatory confirmation for the user to understand their commitment
    $form['fields']['confirmation']=array(
					  '#prefix'=>'<td colspan="2">',
					  '#type'=>'checkbox',
					  '#title'=>t('<b>By checking this box, I understand that I am signing up for this event and am committed to the time(s) I agreed to.</b>'),
					  '#suffix'=>'</td></tr>',
					  );

    $form['fields']['html1'] = array('#markup'=>'<tr>');

    // allows a user to cancel their commitment to their outreach times
    if(!$new){
      $form['fields']['cancel']=array(
				      '#prefix'=>'<td colspan="2">',
				      '#type'=>'submit',
				      '#value'=>'Remove Commitment',
				      '#limit_validation_errors' => array(),
				      '#submit'=>array('cancelCommit'),
				      '#suffix'=>'</td>'
				      );
    } else {
      $form['fields']['html'] = array('#markup'=>'<td></td>');
    }

    $form['fields']['submit']=array(
				    '#prefix'=>'<td colspan="2" style="text-align:right">',
				    '#type'=>'submit',
				    '#value'=>t('Submit'),
				    '#suffix'=>'</td>'
				    );


    $form['fields']['footer'] = array('#markup'=>'</tr></table>');

    return $form;

  } else { // in case the parameter passed was an invalid OID
    drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to naviagte back to events in Team Dashboard.', 'error');
  }

}

// signUp_validate()- Validates signUp form

function signUp_validate($form, $form_state)
{
  $timesFormState = $form_state['values']['times'];
  $allTimesEmpty = true;

  foreach ($timesFormState as $individualTime){ // getting each individual time from the checkboxes clicked
    if(empty($individualTime)){
      $allTimesEmpty = false;
    }
  }
  
  // going through various checks (making sure that at least 1 time and confirmation box are checked)
  if($allTimesEmpty && empty($form_state['values']['confirmation'])){
    if(dbIsUserSignedUp($UID, $OID)){
      form_set_error('times','You must sign up for a time or cancel your commitment.');
    } else {
      form_set_error('times','You must sign up for a time.');
    }
  }

  if($allTimesEmpty == 4 && !empty($form_state['values']['confirmation'])){
    form_set_error('times','You must sign up for a time.');
  }

  if(empty($form_state['values']['confirmation']) && $timesFormState != 4){
    form_set_error('confirmation','You must confirm that you understand your commitment to this outreach.');
  }

}

// signUp_submit()- Submit signUp form

function signUp_submit($form, $form_state)
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  $OID = $params['OID'];
  $new = $form_state['new'];

  $fields = array("times");
  $fieldsData = getFields($fields,  $form_state['values']);
  
  $types  = $fieldsData['times'];
  $types = array_values($types);


  if($new){ // if the user is signing up for the first time
    foreach($types as $type){
      if($type !== 0){
	dbAssignUserToOutreach($UID, $OID, $type); // assigning user to outreach and the various time(s) they signed up for
      }
    } 
    $msgToUser = "You've just signed up for outreach event:";
  }

  if(!$new){ // user is updating the time(s)
    $previous = dbGetUserSignUpType($UID,$OID);
    $deleted = array_diff($previous, $types);
    $added = array_diff($types, $previous);
    foreach($deleted as $delete){
      if(!empty($delete)){
	dbRemoveCommitmentFromOutreach($UID, $OID, $delete); // removing the times the user "unchecked"
      }
    }
    foreach($added as $add){ // adding the times the user "checked"
      if(!empty($add)){
	dbAssignUserToOutreach($UID, $OID, $add);
      }
    }
    $msgToUser = "You're commitment has been updated for outreach event:";
  }

  // sending a notification to the appropriate user(s)
  $notification = array();
  $userName = dbGetUserName($UID);
  $outName = dbGetOutreachName($OID);
  $team = getCurrentTeam();
  $TID = $team['TID'];
  $notification['dateTargeted'] = $notification['dateCreated'] = dbDatePHP2SQL(time());
  $notification['message'] = "$userName has just signed up for your outreach: $outName!";
  $notification['TID'] = $TID;
  notifyOwnerOfOutreach($OID, $notification);

  $outreachEventLink = '<a href="?q=viewOutreach&OID='.$OID.'">'.dbGetOutreachName($OID).'</a>';
  drupal_set_message("$msgToUser $outreachEventLink!");
  drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

// cancelCommit()- Deletes the commitment that a user has made for that outreach (called by signUp() if cancel button clicked)

function cancelCommit()
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

  // removing user's commitment from the outreach completely
  dbRemoveUserFromOutreach($UID,$OID);
  drupal_set_message("You're commitment to outreach event: " . dbGetOutreachname($OID) . " has been removed!"); //letting them know and redirecting user to the previous page they were on
  drupal_goto($_SERVER['HTTP_REFERER']);
}

?>