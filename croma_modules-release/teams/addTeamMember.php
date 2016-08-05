<?php

function addTeamMember($form, &$form_state)
{
  $params = drupal_get_query_parameters();
  if (isset($params['TID'])){
    $form_state['TID'] = $params['TID'];
  }
  if (!isset($form_state['TID'])){
    drupal_set_message('You need to specify a team!', 'error');
  }

  $teamName = dbGetTeamName($form_state['TID']);

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t("Add Team Member to $teamName"),
			);


  $form['fields']['tableHeader']=array(
				       '#markup'=>'<table>'
				       );

  $form['fields']['primaryEmail']=array( // user's email
					'#prefix'=>'<tr><td colspan="2" style="text-align:center">',
					'#type'=>'textfield',
					'#title'=>t('Email'),
					'#suffix'=>'</td></tr><tr><td>',
					'#ajax'=>array(
						       'callback'=>'fillUserName',
						       'keypress'=>true,
						       'wrapper'=>'div_user_name_wrapper',
						       )
					 );

  $form['fields']['name']['firstName']=array( // user's first name
				     '#prefix'=>'<div id="div_user_name_wrapper"><table><tr><td style="text-align:center">',
				     '#type'=>'textfield',
				     '#title'=>t('First Name'),
				     '#suffix'=>'</td>');

  $form['fields']['name']['lastName']=array( // user's last name
				    '#prefix'=>'<td style="text-align:center">',
				    '#type'=>'textfield',
				    '#title'=>t('Last Name'),
				    '#suffix'=>'</td></tr></table></div></td></tr>');

  $form['fields']['submit']=array( // submitting user info/changes
			'#prefix'=>'<td colspan="2" style="text-align:right">',
			'#type'=>'submit',
			'#value'=>t('Save'),
			'#suffix'=>'</td>'
			);


  $form['footer']=array('#markup'=>'</tr></table>');

  return $form;
}

function addTeamMember_validate($form, $form_state)
{
  if(empty($form['fields']['name']['firstName']['#value']) 
     && empty($form_state['values']['firstName'])){
    form_set_error('firstName','First name cannot be empty.');
  }

  if(empty($form['fields']['name']['lastName']['#value']) 
     && empty($form_state['values']['lastName'])){
    form_set_error('lastName','Last name cannot be empty.');
  }

  if(empty($form_state['values']['primaryEmail'])){
    form_set_error('primaryEmail','Email cannot be empty.');
  }
}

function addTeamMember_submit($form, $form_state)
{
  $UID = dbSearchUserByEmail($form_state['values']['primaryEmail']);
  $teamName = dbGetTeamName($form_state['TID']);

  // if the user doesn't exist yet
  if ($UID == false){
    // programmatically creates a Drupal user and sends an email to set-up account
    $UID = createNewUser($form_state);
  } else {
    $params['fullName'] = dbGetUserName($UID);
    $params['teamName'] = $teamName;
    drupal_mail('teams', 'approvedForTeam', $form_state['values']['primaryEmail'], NULL, $params, 'croma@chapresearch.com');
  } 

  $notification = array('UID'=>$UID, 'TID'=>$form_state['TID'], 'dateCreated'=>dbDatePHP2SQL(time()),'dateTargeted'=>dbDatePHP2SQL(time()));
  $notification['message'] = 'You have just been added to ' . $teamName . '!';
  $notification['bttnTitle'] = 'View Team';
  $notification['bttnLink'] = "?q=viewTeam&TID={$form_state['TID']}";
  dbAddNotification($notification);

  dbAssignUserToTeam($UID, $form_state['TID']);

  $userName = dbGetUserName($UID);

  drupal_set_message("$userName has been invited to your team!");
}

// fillTeamName() - Dynamically updates the team name slot based on number given.
function fillUserName(&$form, &$form_state)
{
  $UID = dbSearchUserByEmail($form_state['values']['primaryEmail']);

  // if this user isn't registered with CROMA, just ignore this call
  if ($UID == false){
    return;
  }

  $userProfile = dbGetUserProfile($UID);

  if ($userProfile == false){
    drupal_set_message('This user is invalid. Please inform this user to set up a profile.', 'error');
  } else {
    $firstName = $userProfile['firstName'];
    $lastName = $userProfile['lastName'];
    $form['fields']['name']['firstName']['#value'] = $firstName;
    $form['fields']['name']['lastName']['#value'] = $lastName;
  }

  $form['fields']['name']['firstName']['#attributes']['disabled'] = 'disabled';
  $form['fields']['name']['lastName']['#attributes']['disabled'] = 'disabled';
  $form_state['rebuild'] = TRUE;

  return $form['fields']['name'];
}

function createNewUser($form_state)
{

  //This will generate a random password, you could set your own here
  $password = user_password(8);
 
  $userName = $form_state['values']['firstName'].' '.$form_state['values']['lastName'];

  //set up the user fields
  $fields = array(
		  'name' => $form_state['values']['primaryEmail'],
		  'mail' => $form_state['values']['primaryEmail'],
		  'pass' => $password,
		  'status' => 1,
		  'init' => 'email address',
		  'roles' => array(
				   DRUPAL_AUTHENTICATED_RID => 'authenticated user',
				   ),
		  );
 
  //the first parameter is left blank so a new user is created
  $account = user_save('', $fields);
 
  // Manually set the password so it appears in the e-mail.
  $account->password = $fields['pass'];
 
  // Send the e-mail through the user module.
  $params['url'] = user_pass_reset_url($account);
  $params['teamName'] = dbGetTeamName($form_state['TID']);
  drupal_mail('users', 'userCreated', $form_state['values']['primaryEmail'], NULL, $params, 'croma@chapresearch.com');

  $fields = array('firstName', 'lastName');
  $profileData = getFields($fields, $form_state['values']);
  $profileData = stripTags($profileData, '');
  $profileData['UID'] = $account->uid;

  dbCreateProfile($profileData); // creating new profile

  return $profileData['UID'];
}

?>