<?php

/*
  ---- teams/applyForTeams.php ----

  used to delete a team (and display the appropriate warnings)

  - Contents -
  deleteTeamPage() - paints the page explaining the consequences of deleting a team
*/

function deleteTeamPage($form, &$form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  
  if(isset($params['TID'])){
    $TID = $form_state['TID'] = $params['TID'];
  } else {
    drupal_set_message('You must specify a team.', 'error');
    return;
  }

  // check permissions
  if(!dbUserHasPermissionForTeam($user->uid, 'deleteTeam', $TID)) {
    drupal_set_message('You do not have permission to delete this team.', 'error');
    return;
  }

  $teamName = dbGetTeamName($form_state['TID']);

  $form['header'] = array(
			  '#markup'=>"<h1>Delete Team $teamName</h1>"
			  );

  $form['warning'] = array(
			   '#markup'=>t('The team will be deleted and all users will be removed. Note however that the outreaches created will remain, as will the hours logged. This is to maintain correct hour logging for users with multiple teams. The outreaches will be neither viewable nor editable however.')
			   );
  
  $form['acknowledgement'] = array(
				   '#type'=>'checkbox',
				   '#title'=>t('By checking this box, I acknowledge that I am permanently deleting this team and have the permissions to perform such an action.')
				   );
  $form['misc'] = array(
			'#type'=>'textarea',
			'#title'=>t('Feedback')
			);

  $form['delete'] = array(
			  '#prefix'=>'<div align="right">',
			  '#type'=>'submit',
			  '#value'=>'Delete Team',
			  '#suffix'=>'</div>'
			  );

  return $form;
}

function deleteTeamPage_validate($form, $form_state)
{
  if($form_state['values']['acknowledgement'] == 0){
    form_set_error('checkbox', 'You must acknowledge that you are intentionally deleting this team.');
  }
}

function deleteTeamPage_submit($form, $form_state)
{
  global $user;
  $UID = $user->uid;

  $TID = $form_state['TID'];

  if(dbUserHasPermissionForTeam($user->uid, 'deleteTeam', $TID)) {
    dbDeactivateTeam($TID);
    dbKickAllUsersFromTeam($TID);
    dbRemoveAllRolesFromTeam($TID);
  } else {
    drupal_set_message('You do not have permission to perform this action.', 'error');
    return;
  }

  // send an email to the CROMA team detailing the team deletion
  $params['feedback'] = stripTags($form_state['values']['misc'], '');
  $params['userName'] = dbGetUserName($UID);
  $params['teamName'] = dbGetTeamName($TID);
  $params['teamNumber'] = dbGetTeamNumber($TID);

  drupal_mail('teams', 'teamDeleted', 'croma@chapresearch.com', variable_get('language_default'), $params, $from = NULL, $send = TRUE);

  drupal_set_message(dbGetTeamName($TID) . " has been deleted.");

  drupal_goto('<front>');
}

?>