<?php

   /*
     ---- hours/hourForm.php ----
     used to allow users to add/edit hours

     - Contents -
     addHourRow() - helper function for adding rows through AJAX
     removeHourRow() - helper function for removing rows through AJAX
     hoursForm() - form to allow entering/editing of old, pre-CROMA hours 
     hoursForm_validate() - checks that the years and hour counts are reasonable
     hoursForm_submit() - adds/edits the old hours
   */   

   // adds new row
function addHourRow($form, &$form_state) {
  $form_state['numRows']++;
  $form_state['rebuild'] = TRUE;
   }

// deletes row
function removeHourRow($form, &$form_state) {
  $form_state['numRows']--;
  $form_state['rebuild'] = TRUE;
}

function modifyHourRows_callback($form, $form_state) {
  return $form['fields'];
}

function hoursForm($form, &$form_state)  
{  
  global $user;

  $params = drupal_get_query_parameters();
  $new = isset($form_state['new'])?$form_state['new']:true;
  
  // checking that you are on a team
  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if(isset($params['HID'])){
    $new = false;     // editing an existing hours record
    $form_state['new'] = $new;    // update form_state to reflect this
  }
 
  // setting values...
  // creating a new record
  if($new){ 
    if (isset($form_state['OID'])){
      $OID = $form_state['OID'];
    } else {
      $OID = $params['OID'];
      $form_state['OID'] = $OID;
    }
    $UID = $user->uid;
    // editing an existing hours record
  } else { 
    if(isset($form_state['HID'])){
      $HID = $form_state['HID'];
    } else {
      $HID = $params['HID'];
      $form_state['HID'] = $HID;
    }
    $hour = dbGetHour($HID);
    $UID = $hour['UID'];
    $OID = $hour['OID'];
    $form_state['OID'] = $OID;
    if ($hour['isApproved']){
      drupal_set_message('Note that this hour will have to be re-approved once changes are made.', 'error');
    }
  }

  // checking that you are accessing this page with a valid outreach
  if (!isset($OID) && !isset($params['HID'])){
    drupal_set_message("No outreach selected.", 'error');
    return;
  }

  // checking that you are associated with the team for which this outreach belongs to
  $TID = dbGetTeamForOutreach($OID);
  if (!isMyTeam($TID)){
    $teamName = dbGetTeamName($TID);
    drupal_set_message("You are no longer on the team associated with this outreach ($teamName).", 'error');
    return;
  }

  // checks if you have permission to edit hours
  if(!(hasPermissionForTeam('editAnyHours', dbGetTeamForOutreach($OID))
       || isMyOutreach($OID) 
       || dbIsUserSignedUp($user->uid, $OID))){
    drupal_set_message("You don't have permission to log hours for this outreach.", 'error');
    return;
  }

  if (isset($form_state['OID']) || isset($params['HID'])){

    $outreachData = dbGetOutreach($OID);

    //begins form
    $form['fields']=array(
			  '#prefix'=>'<div id="rows-div">',
			  '#type'=>'fieldset',
			  '#title'=>t("Enter Hours For Outreach: \"{$outreachData['name']}\""),
			  '#suffix'=>'</div>',
			  );

    $users = dbGetUsersListFromTeam($TID);
    $users['0'] = '[none]';

    if(hasPermissionForTeam('editAnyHours', $TID)){
      $form['fields']['UID']=array(
				   '#prefix'=>'<td>',
				   '#type'=>'select',
				   '#title'=>t('User:'),
				   '#options'=>$users,
				   '#attributes'=>array('style'=>'width:200px'),
				   '#default_value'=>$UID,
				   '#chosen'=>true,
				   '#suffix'=>'</td>'
				   );
    }

    $form['fields']['tableHeader']=array(
					 '#markup'=>'<table>'
					 );

    if (empty($form_state['numRows'])) {
      $form_state['numRows'] = 1;
    }

    if(!hasPermissionForTeam('editAnyHours', $TID)){
      $signUpInfo = dbGetUserSignUpInfo($UID,$OID);
      $numSignUps = count($signUpInfo);
      $signUpCountLoop = 0;
      $type = array();
      foreach($signUpInfo as $info){
	if($numSignUps > 1 && ($signUpCountLoop != $numSignUps-1) && ($form_state['numRows'] != $numSignUps)){
	  $form_state['numRows']++;
	}

	if($info['type'] == 'prep'){
	  $type[$signUpCountLoop] = array('prep'=>'Preparation');
	} else if($info['type'] == 'atEvent'){
	  $type[$signUpCountLoop] = array('atEvent'=>'At Event');
	} else if($info['type'] == 'writeUp'){
	  $type[$signUpCountLoop] = array('writeUp'=>'Write-Up');
	} else if($info['type'] == 'followUp'){
	  $type[$signUpCountLoop] = array('followUp'=>'Follow Up');
	} else if($info['type'] == 'other'){
	  $type[$signUpCountLoop] = array('prep'=>'Other');
	}
	$signUpCountLoop ++;
      }
    }

    // looping through the rows
    for($i = 0; $i < $form_state['numRows']; $i++){

      $rowMarkUp = "<tr id=\"row-$i\"";
      $rowMarkUp .= '>';
      $form['fields']["rowHeader-$i"]=array(
					    '#markup'=> $rowMarkUp
					    );


      $form['fields']["numberOfHours-$i"]=array(
						'#prefix'=>'<td  colspan="1" style="text-align:left;width:20%;">',
						'#type'=>'textfield',
						'#title'=>t('Number Of Hours:'),
						'#suffix'=>'</td>',
						'#default_value'=>$new?'':$hour['numberOfHours']
						);

      if(hasPermissionForTeam('editAnyHours', $TID)){
	$types = array('prep'=>'Preparation','atEvent'=>'At Event','writeUp'=>'Write-Up','followUp'=>'Follow Up', 'other'=>'Other');
      } else {
	$types = $type[$i];
      }

      $form['fields']["type-$i"]=array(
				       '#prefix'=>'<td colspan="1" style="text-align:left; width:20%;">',
				       '#type'=>'radios',
				       '#title'=>t('Type:'),
				       '#options'=> $types,
				       '#suffix'=>'</td>',
				       '#default_value'=>$new?'':$hour['type']
				       );

      $form['fields']["description-$i"]=array(
					      '#prefix'=>'<td colspan="3" style="text-align:left; width:50%;">',
					      '#type'=>'textarea',
					      '#title'=>t('Description:'),
					      '#suffix'=>'</td>',
					      '#default_value'=>$new?'':$hour['description']
					      );

      if($i == $form_state['numRows'] - 1){
	$form['fields']["addRowButton-$i"]=array(
						 '#prefix'=>'<td colspan="1" style="width:5%">',
						 '#type'=>'submit',
						 '#submit'=>array('addHourRow'),
						 '#value'=>'+',
						 '#limit_validation_errors' => array(),
						 '#ajax'=>array(
								'callback'=>'modifyHourRows_callback',
								'wrapper'=>'rows-div'
								),
						 '#suffix'=>'</td>'
						 );
	$form['fields']["removeRowButton-$i"]=array(
						    '#prefix'=>'<td colspan="1" style="width:5%">',
						    '#type'=>'submit',
						    '#submit'=>array('removeHourRow'),
						    '#value'=>'-',
						    '#limit_validation_errors' => array(),
						    '#ajax'=>array(
								   'callback'=>'modifyHourRows_callback',
								   'wrapper'=>'rows-div'
								   ),
						    '#suffix'=>'</td>'
						    );
      }
      $form['fields']['rowFooter']=array('#markup'=>'</tr>');
      // end of for loop
    }

    $form['fields']['submit']=array(
				    '#prefix'=>'<tr><td colspan="7" style="text-align:right">',
				    '#type'=>'submit',
				    '#value'=>t('Submit'),
				    '#suffix'=>'</td></tr>',
				    );

    $form['fields']['tableFooter']=array(
					 '#markup'=>'</table>'
					 );

  }

  return $form;
}

// form validation for the hours
function hoursForm_validate($form, $form_state) 
{
  for($i = 0; $i < $form_state['numRows']; $i++){
    if(empty($form_state['values']["numberOfHours-$i"])){
      // hours cannot be empty
      form_set_error("fields][numberOfHours-$i",'Number of hours cannot be empty.');
    }
  }
  for($i = 0; $i < $form_state['numRows']; $i++){
    if(!empty($form_state['values']["numberOfHours-$i"])){
      if(!ctype_digit($form_state['values']["numberOfHours-$i"])){
	// hours cannot be empty
	form_set_error("fields][numberOfHours-$i",'You must input only numeric digits.');
      }
    }
  }
  // type cannot be empty
  for($i = 0; $i < $form_state['numRows']; $i++){
    if(empty($form_state['values']["type-$i"])){
      form_set_error("fields][type-$i",'Type cannot be empty.'); 
    }
  }
}

function hoursForm_submit($form, $form_state)
{
  global $user;
  
  // getting value of new from form state
  $new = isset($form_state['new'])?$form_state['new']:true; 
  $OID = $form_state['OID'];

  // looping through the rows of hours
  for($i = 0; $i < $form_state['numRows']; $i++){
    $fields = array("numberOfHours-$i","description-$i","type-$i");
    $row = getFields($fields,$form_state['values']);
    // dont allow any html tags
    $row = stripTags($row, ''); 

    // setting the values which were read in into the row which will go into the database
    $row['numberOfHours'] = $row["numberOfHours-$i"];
    $row['description'] = $row["description-$i"];
    $row['isApproved'] = 0;
    $row['type'] = $row["type-$i"];
    unset($row["type-$i"], $row["numberOfHours-$i"], $row["description-$i"]);

    if(isset($form_state['values']['fields']['UID'])){
      $UID = $form_state['values']['fields']['UID'];
    } else {
      $UID = $user->uid;
    }
    if($UID != 0){
      $row['UID'] = $UID;
    }
    $row['OID'] = $OID;

    // if adding new hours
    if($new){
      if (dbLogHours($row) == false){
	drupal_set_message("Error", 'error');
	break;
      }
    } else {      // editing old hours
      $row['isApproved'] = 0;
      if (dbUpdateHours($row, $form_state['HID']) == false){
	drupal_set_message("Error", 'error');
	break;
      }
    }
  } // end of for loop

  drupal_set_message("Your hours have been logged!");

  // assigning user to outreach if not new
  if(!$new){ 
    dbAssignUserToOutreach($UID, $OID, $row['type']);
    drupal_goto("viewOutreach", array('query'=>array("OID"=>$OID)));
  } else {    // notifying appropriate users of changes/addition of hours
    $outreachName = dbGetOutreachName($OID);
    $personName = dbGetUserName($user->uid);
    $notification['message'] = "$personName has logged hours for $outreachName!";
    $notification['TID'] = dbGetTeamForOutreach($OID);
    $notification['dateTargeted'] = dbDatePHP2SQL(time());
    $notification['dateCreated'] = dbDatePHP2SQL(time());
    notifyUsersByRole($notification, 'moderator');
    notifyOwnerOfOutreach($OID, $notification);
    if ($OID != 0){
      drupal_goto("viewHours", array('query'=>array("OID"=>$OID)));
    } else {
      drupal_goto("viewHours", array('query'=>array("UID"=>$UID)));
    }
  }
}

?>