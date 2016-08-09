<?php

/*
  ---- teams/applyForTeams.php ----

  used for creating (and auto-filling) form to apply to teams

  - Contents -
  applyForTeamForm() - allows user to apply for membership on a team
  fillTeamName() - dynamically updates the team name slot based on number given
*/

// applyForTeamForm() - Allows user to apply for membership on a team.
function applyForTeamForm()
{
  global $user;
  $params = drupal_get_query_parameters();
  $profile = dbGetUserProfile($user->uid);

  if(isset($params['url'])){
    $form['back']=array(
		      '#markup'=>"<a href=\"?q={$params['url']}\"><button type=\"button\">Cancel</button></a>"
		      );
  }

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Apply to Join a Team'),
			);

  $form['fields']['tableHeader']=array(
				       '#markup'=>'<table>'
				       );
  $form['fields']['number']=array(
				  '#prefix'=>'<tr><td>',
				  '#type'=>'textfield',
				  '#title'=>t('Team Number:'),
				  '#suffix'=>'</td><td>',
				  '#ajax'=>array(
						 'callback'=>'fillTeamName',
						 'keypress'=>true,
						 'wrapper'=>'div_team_name_wrapper',
						 )
				  );

  // this form will be filled in via AJAX
  $form['fields']['teamName']=array(
				    '#prefix'=>'<div id="div_team_name_wrapper">',
				    '#type'=>'textfield',
				    '#title'=>t('Team Name:'),
				    '#disabled'=>true,
				    '#suffix'=>'</div>'
				    );
  
  // this form is filled in from previous data
  $form['fields']['personName']=array(
				      '#prefix'=>'</td></tr><tr><td>',
				      '#type'=>'textfield',
				      '#title'=>t('Your Name:'),
				      '#default_value'=>"{$profile['firstName']} {$profile['lastName']}",
				      '#disabled'=>true,
				      '#suffix'=>'</td>'
				      );

  // user email is filled in, but still editable
  $form['fields']['email']=array(
				 '#prefix'=>'<td>',
				 '#type'=>'textfield',
				 '#title'=>t('Your Email:'),
				 '#default_value'=>dbGetUserPrimaryEmail($user->uid),
				 '#suffix'=>'</td></tr>'
				 );

  $defaultMessage = "Hi, I'd like to join your team!";

  $form['fields']['message']=array(
				   '#prefix'=>'<tr><td colspan="2">',
				   '#type'=>'textarea',
				   '#title'=>t('Personal Message:'),
				   '#default_value'=>$defaultMessage,
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

function applyForTeamForm_validate($form, &$form_state)
{
  $team = dbGetTeamByNumber($form_state['values']['number']);

  if(empty($team)){
    form_set_error('teamName', 'Please select a valid team.');
  } else if(isMyTeam($team['TID'])){
    form_set_error('teamName', 'You are already on this team!');
  } else {
    $TID = $team['TID'];
    $form_state['TID'] = $TID;
  }
}

function applyForTeamForm_submit($form, $form_state)
{
  global $user;

  $name = $form_state['values']['personName'];
  $email = $form_state['values']['email'];
  $teamName = dbGetTeamName($form_state['TID']);
  $note = $form_state['values']['message'];

  // fill in the fields of the application
  $application['UID'] = $user->uid;
  $application['TID'] = $form_state['TID'];
  $application['userEmail'] = stripTags($email, ''); // do not allow tags
  $application['userMessage'] = stripTags($note); // allow some tags
  
  // add a notification for the team owner and admins
  if(dbApplyForTeam($application)) { // note that this does its own error checking
    $notification['dateCreated'] = dbDatePHP2SQL(time());
    $notification['dateTargeted'] = dbDatePHP2SQL(time());
    $notification['TID'] = $form_state['TID'];
    $notification['message'] = "$name has applied to join your team $teamName.";
    $notification['bttnTitle'] = 'View';
    $notification['bttnLink'] = '?q=viewUsersToBeApproved&TID='.$form_state['TID'];

    notifyUsersByRole($notification, 'teamOwner');
    notifyUsersByRole($notification, 'teamAdmin');

    drupal_set_message('Your application has been sent! You will receive an email when you have been approved for the team.');
    drupal_goto('manageUserTeams');
  }
}

// fillTeamName() - Dynamically updates the team name slot based on number given.
function fillTeamName(&$form, &$form_state)
{
  $team = dbGetTeamByNumber($form_state['values']['number']);
  $teamName = isset($team['name'])?$team['name']:'NOT REGISTERED WITH CROMA';
  $form['fields']['teamName']['#value'] = $teamName;
  $form_state['rebuild'] = true;
  return $form['fields']['teamName'];
}

?>