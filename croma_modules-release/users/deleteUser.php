<?php

/*
  ---- users/deleteUser.php ----

  functions to display various data on the home page shown when not logged in

  - Contents -
  deleteUserPage() - warns the user about deleting his/her account and allows user to provide feedback
*/

function deleteUserPage($form, &$form_state)
{
  $form['header'] = array( // general header
			  '#markup'=>'<h1>Delete User Account</h1>'
			  );

  $form['warning'] = array( // warning message to make sure that the user really wants to delete their account
			   '#markup'=>t('You will no longer be able to log into CROMA, and will be removed from all your associated teams. Note that your members will still be able to view your hours and outreaches, but not your profile.')
			   );
  
  $form['acknowledgement'] = array( // check box to ensure that they understand the warning
				   '#type'=>'checkbox',
				   '#title'=>t('By checking this box, I acknowledge that I am permanently deleting my account.')
				   );
  $form['misc'] = array( // any feedback as to why the user is leaving
			'#type'=>'textarea',
			'#title'=>t('Feedback')
			);

  $form['delete'] = array( // button which actually deletes the user
			  '#prefix'=>'<div align="right">',
			  '#type'=>'submit',
			  '#value'=>'Delete account',
			  '#suffix'=>'</div>'
			  );

  return $form;
}

function deleteUserPage_validate($form, $form_state)
{
  if($form_state['values']['acknowledgement'] == 0){ // ensuring that the user has checked the checkbox
    form_set_error('checkbox', 'You must acknowledge that you are intentionally deleting your account');
  }
}

function deleteUserPage_submit($form, $form_state)
{
  global $user;
  $UID = $user->uid;

  $teams = dbGetTeamsForUser($UID); // getting teams that are associated with a user

  foreach($teams as $team){ // looping through these teams
    dbKickUserFromTeam($UID,$team['TID']); // removing the user from these teams
    dbRemoveAllUserRoles($UID, $team['TID']); // ensuring the user doesn't have any role on the team
  }

  dbRemoveAllEmailsForUser($UID);

  dbDisableUser($UID);

  $params['feedback'] = stripTags($form_state['values']['misc'], ''); // stripping any "illegal" HTML tags
  $params['userName'] = dbGetUserName($UID); // getting the user name

  drupal_mail('users', 'userdeleted', 'croma@chapresearch.com', variable_get('language_default'), $params, $from = null, $send = true); // sending the user a confirmation mail

  drupal_set_message("Your account has been deleted. We're sorry to see you go!"); // message displayed and redirected to front page
  drupal_goto('<front>');
}
?>