<?php

/*
  ---- teams/publicOutreachForTeams.php ----

  used to change outreach visibilities

  - Contents -
  publicOutreach() - allows a user to change outreach visibilities
  backtosettings() - takes the user back to team outreach settings
*/

function publicOutreach($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $TID = getCurrentTeam()['TID'];
  $outreaches = dbGetLockedOutreachForTeam($TID);

  // checking to make sure user has permission to change team outreach settings
  if(!(hasPermissionForTeam('editAnyOutreach', $TID))){
    drupal_set_message("You don't have permission to change outreach settings for this team!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // checking to see if the user has a team assigned
  if(dbGetTeamsForUser($user->uid) == false){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // checking to see if the user is approved for the team
  if(!(dbIsUserApprovedForTeam($UID, $TID))){
    drupal_set_message("You aren't approved for this team.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // checking to see if the team is active
  if(dbGetStatusForTeam($TID) == "0" || dbGetStatusForTeam($TID) == false){
    drupal_set_message("This team isn't active/approved!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $names = array();
  $data = array();

  // if team has locked outreaches
  if(!empty($outreaches)){
    foreach($outreaches as $outreach){
      $names[$outreach['OID']] = $outreach['name'];
      if ($outreach['isPublic']){
	$data[] = $outreach['OID'];
      }
    }
  }

  // begin form  
  $form = array();

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Changing Visibilities For ' . dbGetTeamNumber($TID)),
			);

  $new = false;

  // cancel changes button doesn't save anything and goes back to team outreach settings
  if (!$new){
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToSettings'),
				  '#type' => 'submit',				
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }

  // if the team has locked outreaches
  if(!empty($outreaches)){
    $form['fields']['outreaches']=array(
					'#prefix'=>'<table><tr><td>',
					'#type'=>'checkboxes',
					'#title'=>t('<h5>Which outreaches would you like to make public?</h5>'),
					'#options'=> $names,
					'#default_value'=>$data,
					'#suffix'=>'</td></tr><tr><td><br>Only showing outreaches which are "locked".</td></tr>',
					'#checkall' => true,
					);
    $form['fields']['footer'] = array('#markup'=>'</table>');
    $form['fields']['submit']=array(
				    '#prefix'=>'<table><tr><td colspan="3" style="text-align:right">',
				    '#type'=>'submit',
				    '#value'=>t('Save'),
				    '#suffix'=>'</td></tr></table>'
				    );
  } else {

    // if the team does not have any locked outreaches
    $form['fields']['outreaches']=array(
					'#prefix'=>'<table><tr><td colspan="3" style="text-align:left">',
					'#markup'=>"Your team doesn't have any locked outreaches!<br>",
					'#suffix'=>'</td></tr></table>'
					);
  }
   
  return $form;
}


function publicOutreach_submit($form, $form_state)
{
  $previousOIDs = $form['fields']['outreaches']['#default_value'];

  $currentOIDs = array_values($form_state['values']['outreaches']);

  $deleted = array_diff($previousOIDs, $currentOIDs);
  $added = array_diff($currentOIDs, $previousOIDs);

  foreach($deleted as $delete){
    if(!empty($delete)){
      dbSetOutreachToPrivate($delete);
    }
  }
  foreach($added as $add){
    if(!empty($add)){
      dbSetOutreachToPublic($add);
    }
  }
}

function backToSettings()
{
  drupal_goto("teamOutreachSettings");
}

?>
