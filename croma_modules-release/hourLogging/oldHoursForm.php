<?php

/*
  ---- hours/oldHoursForm.php ----
  used to allow users to add old hours (aka pre-CROMA)

  - Contents -
  addOldHourRow() - helper function for adding rows through AJAX
  removeOldHourRow() - helper function for removing rows through AJAX
  oldHoursForm() - form to allow entering/editing of old, pre-CROMA hours 
  oldHoursForm_validate() - checks that the years and hour counts are reasonable
  oldHoursForm_submit() - adds/edits the old hours
*/   

function addOldHourRow($form, &$form_state) {
  $form_state['numRows']++;
  $form_state['rebuild'] = true;
}

function removeOldHourRow($form, &$form_state) {
  $form_state['numRows']--;
  $form_state['rebuild'] = true;
}

function modifyOldHourRows_callback($form, $form_state) {
  return $form['fields'];
}

function oldHoursForm($form, &$form_state)  
{  
  global $user;

  $params = drupal_get_query_parameters();

  // initialization of values
  if (!isset($form_state['TID'])){ // first time on form
    $TID = $form_state['TID'] = $params['TID']; // set to url params
    $offset = dbGetOffsetHours($TID);    
    if($offset != null){
      $new = $form_state['new'] = false;
      $form_state['numRows'] = count($offset);
      $form_state['initialNumTimes'] = count($offset); // record how many were there before (in case user deletes one)
    } else {
      $new = $form_state['new'] = true;
    }
  } else { // not first time on form
    $TID = $form_state['TID'];
    $offset = dbGetOffsetHours($TID);
  }

  // checking that user is on a team
  if(dbGetTeamsForUser($user->uid) == false)
    {
      drupal_set_message("You don't have a team assigned!", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
    }

  // checking that you are accessing this page with a team in mind
  if (!isset($TID)){ // if $TID is still null
    drupal_set_message("No team selected!", 'error');
    return;
  }

  // checking to make sure you have permission to actually edit the offset hours
  if (!(isMyTeam($TID)) || !(hasPermissionForTeam('editTeam', $TID))){
    drupal_set_message("You don't have permission to edit the 'old hours' for this team!", 'error');
    return;
  }

  // beginning the form itself
  $form['fields']=array(
			'#prefix'=>'<div id="oldHourRows-div">',
			'#type'=>'fieldset',
			'#title'=>t('Offsetting Hours For Team: ' . dbGetTeamName($TID)),
			'#suffix'=>'</div>',
			);

  if (!$new){ // if user wants to cancel any changes they made
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToTeam'),
				  '#type' => 'submit',
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }

  $form['fields']['tableHeader']=array(
				       '#markup'=>'<table>'
				       );

  if (empty($form_state['numRows'])) {
    $form_state['numRows'] = 1;
  }

  
  for($i = 0; $i < $form_state['numRows']; $i++){

    $rowMarkUp = "<tr id=\"row-$i\"";
    $rowMarkUp .= '>';
    $form['fields']["rowHeader-$i"]=array(
					  '#markup'=> $rowMarkUp
					  );

    $form['fields']["hours-$i"]=array(
				      '#prefix'=>'<td style="text-align:left;">',
				      '#type'=>'textfield',
				      '#title'=>t('Number Of Hours:'),
				      '#suffix'=>'</td>',
				      '#default_value'=>$new?'':$offset[$i]['numberOfHours']
				      );

    if(!$new){
      $form_state['fields']["HTID-$i"] = $offset[$i]['HTID'];
    }

    $form['fields']["year-$i"]=array(
				     '#prefix'=>'<td style="text-align:left;">',
				     '#type'=>'textfield',
				     '#title'=>t('Year:'),
				     '#suffix'=>'</td>',
				     '#default_value'=>$new?'':$offset[$i]['year']
				     );

    if($i == $form_state['numRows'] - 1){
      $form['fields']["addRowButton-$i"]=array(
					       '#prefix'=>'<td>',
					       '#type'=>'submit',
					       '#submit'=>array('addOldHourRow'),
					       '#value'=>'+',
					       '#limit_validation_errors' => array(),
					       '#ajax'=>array(
							      'callback'=>'modifyOldHourRows_callback',
							      'wrapper'=>'oldHourRows-div'
							      ),
					       '#suffix'=>'</td>'
					       );
    }
    if($i == $form_state['numRows'] - 1){
      $form['fields']["removeRowButton-$i"]=array(
						  '#prefix'=>'<td>',
						  '#type'=>'submit',
						  '#submit'=>array('removeOldHourRow'),
						  '#value'=>'-',
						  '#limit_validation_errors' => array(),
						  '#ajax'=>array(
								 'callback'=>'modifyOldHourRows_callback',
								 'wrapper'=>'oldHourRows-div'
								 ),
 						  '#suffix'=>'</td>'
						  );
    }

    $form['fields']['rowFooter']=array('#markup'=>'</tr>');
      
  } // end of for loop


  $form['fields']['submit']=array(
				  '#prefix'=>'<tr><td colspan="4" style="text-align:right">',
				  '#type'=>'submit',
				  '#value'=>t('Submit'),
				  '#suffix'=>'</td></tr>'
				  );
    
  $form['fields']['tableFooter']=array(
				       '#markup'=>'</table>'
				       );
  return $form;
}

function oldHoursForm_validate($form, $form_state)
{
  $currentYear = date("Y");

  for($i = 0; $i < $form_state['numRows']; $i++){

    // hours field cannot be blank
    if (empty($form_state['values']["hours-$i"])){
      form_set_error("fields][hours-$i",'Number of hours cannot be empty.'); 
    } else {
      // hours field must be a number
      if(!is_numeric($form_state['values']["hours-$i"])){
	form_set_error("fields][hours-$i",'Hours field must be a number.'); 
      } else {
	// the number can't be too big
	if($form_state['values']["hours-$i"]> 9999){
	  form_set_error("fields][hours-$i",'Please enter a valid number of hours less than 9,999.'); 	  
	}
      }	
    }

    $rookieYear = dbGetTeam($form_state['TID'])['rookieYear'];

    // year field cannot be blank
    if (empty($form_state['values']["year-$i"])){
      form_set_error("fields][year-$i",'Year cannot be empty.'); 
    } else {
      // year field must be a number
      if(!is_numeric($form_state['values']["year-$i"])){
	form_set_error("fields][year-$i",'Year field must be a number.'); 
      } else {
	// year field cannot be before 1980 (the year FIRST was founded)
	if ($form_state['values']["year-$i"] < 1980){
	  form_set_error("fields][year-$i",'Please enter a valid year after 1980.'); 
	}
	// year field cannot be before the team's rookie year
	if($form_state['values']["year-$i"] < $rookieYear){
	  form_set_error("fields][year-$i",'Please enter a valid year after your rookie year ' . "($rookieYear)");
	}
	// cannot log hours for years in the future
	if ($form_state['values']["year-$i"] > $currentYear){
	  form_set_error("fields][year-$i",'You can not enter an year after ' . $currentYear . '.'); 
	}
      }
    }
  } // end of for loop
}

function oldHoursForm_submit($form, $form_state)
{
  // getting info from the form state
  $new = $form_state['new'];
  $TID = $form_state['TID'];


  // looping through the rows of hours and years
  for($i = 0; $i < $form_state['numRows']; $i++){
    $HTID = isset($form_state['fields']["HTID-$i"])?$form_state['fields']["HTID-$i"]:0;
    $fields = array("hours-$i","year-$i");
    $fields = getFields($fields,$form_state['values']);
    $fields = stripTags($fields, ''); // remove all HTML tags

    // setting the values which were read in into the row which will go into the database
    $row['numberOfHours'] = $fields["hours-$i"];
    $row['year'] = $fields["year-$i"];
    $row['TID'] = $TID;

    // checking to make sure neither of them are null
    if ($row['numberOfHours'] != null && $row['year'] != null){
      if($HTID != 0){ // update existing record
	dbUpdateOffset($HTID, $row);
	$updated = true;
      } else{ // adding new hours
	dbAddHourOffset($row);
	$added = true;
      }
    }
  } // end of for loop

    // executes if times were deleted
  if(!$new){
    for($i = $form_state['numRows']; $i < $form_state['initialNumTimes']; $i++){ 
      $result = dbRemoveOldHours($form_state['fields']["HTID-$i"]);

      $removed = true;
    }
  }

  // display appropriate message
  if (isset($updated) || (isset($added) && isset($removed))){
    drupal_set_message('Your hours have been updated!');
  } else if ($added){
    drupal_set_message("Your hours have been logged!");
  } else if ($removed){
    drupal_set_message("Your hours have been removed!");
  }

  drupal_goto('viewTeam', array('query'=>array('TID'=>$TID)));
}




?>