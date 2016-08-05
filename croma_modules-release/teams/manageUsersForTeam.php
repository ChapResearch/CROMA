<?php

function viewUsersAwaitingApproval()
{
  global $user;
  $params = drupal_get_query_parameters();
  if(isset($params['TID'])){
    $TID = $params['TID'];
    $teamNumber = dbGetTeamNumber($TID);
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
    $teamNumber = $team['number'];
  }
  
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  $markup = '<table><tr><td colspan="3">';
  $markup .= "<h1>Users Awaiting Approval for $teamNumber</h1></td>";
  $markup .= '<td colspan="3" style="text-align:right">';
  $markup .= '<a href= "?q=showUsersForTeam">';
  $markup .= '<button type="button">View All Users</button></a></td></tr><table>';  


  $markup .= '<table class="infoTable"><th>Name</th><th>Email</th><th>Message</th><th></th></tr>';
  $users = dbGetUsersAwaitingApproval($TID);

  if(!empty($users)){

    foreach($users as $person) {
      $markup .= "<tr><td>{$person['firstName']} {$person['lastName']}</td>";
      $markup .= '<td>' . $person['userEmail'] . '</td>';
      $markup .= '<td>' . $person['userMessage'] . '</td>';
      $markup .= '<td><a href="?q=approveUser/' . "{$person['UID']}/$TID"  . '">';
      $markup .= '<button>Approve</button></a>';
      $markup .= '<a href="?q=rejectUser/' . "{$person['UID']}/$TID"  . '">';
      $markup .= '<button>Reject</button></a></td></tr>';
    }

  }else{

    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
    $markup .= "</tr>";
  }

  $markup .= '</table>';
  return array('#markup'=>$markup);
}

function transferTeamOwnershipForm($form, &$form_state)
{
  global $user;
  $params = drupal_get_query_parameters();

  if(isset($params['TID'])){
    $form_state['TID'] = $TID = $params['TID'];
  } else {
    drupal_set_message('You need to choose a team!', 'error');
    drupal_goto('showUsersForTeam');
  }

  $team = dbGetTeam($TID);

  if($user->uid != $team['UID']){ // if the user is not the team owner
    drupal_set_message("You are not the owner of team {$team['name']}", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }    

  $people = dbGetUsersListFromTeam($TID);

  unset($people[$user->uid]); // shouldn't be able to transfer to self

  $people[0] = ''; // needs a default value

  $form['header']=array(
			'#markup'=>"<h1>Transfer Ownership of {$team['name']}</h1>"
			);

  $form['fields']=array(
			'#type'=>'fieldset'
			);

  $form['fields']['table']=array(
		       '#prefix'=>'<table><tr>',
		       '#suffix'=>'</tr></table>');

  $form['fields']['table']['info']=array(
		      '#prefix'=>'<td>',
		      '#markup'=>'Transferring ownership of this team will make you into a team admin (at which point you may leave the team if you wish). The new owner you choose will be notified of their promotion via email, and will gain the <strong>ability to delete the team at will</strong>. His/her notification email will also include your email for contact purposes in case of an error. If you wish to become the team owner again, the new owner must transfer ownership.',
		      '#suffix'=>'</td>'
		      );

  $form['fields']['table']['newOwner']=array(
			  '#prefix'=>'<td>',
			  '#type'=>'select',
			  '#title'=>t('New Team Owner:'),
			  '#default_value'=>0,
			  '#options'=>$people,
			  '#chosen'=>true,
			  '#suffix'=>'</td>',
			  );

  $form['submit']=array(
			'#prefix'=>'<div align="right">',
			'#attributes'=>array('onclick'=>'return confirm("Are you sure you want to transfer ownership?");'),
			'#type'=>'submit',
			'#value'=>'Submit',
			'#suffix'=>'</div>'
			);

  return $form;
}

function transferTeamOwnershipForm_validate($form, $form_state)
{
  if($form_state['values']['newOwner'] == 0){
    form_set_error('newOwner', 'You must choose a new owner!');
  }
}

function transferTeamOwnershipForm_submit($form, $form_state)
{
  global $user;

  $newOwnerUID = $form_state['values']['newOwner'];
  $TID = $form_state['TID'];
  dbUpdateTeam($TID, array('UID'=>$newOwnerUID));
  dbUpdateUserRole($newOwnerUID, $TID, dbGetRID('teamOwner'));
  dbUpdateUserRole($user->uid, $TID, dbGetRID('teamAdmin'));

  $newOwnerName = dbGetUserName($newOwnerUID);
  $teamName = dbGetTeamName($TID);
  drupal_set_message("$newOwnerName is now the owner of $teamName!");

  // notify new owner through CROMA
  $notification = array('UID'=>$newOwnerUID, 'TID'=>$TID, 'dateTargeted'=>dbDatePHP2SQL(time()), 'dateCreated'=>dbDatePHP2SQL(time()));
  $notification['message'] = "You are now the owner of $teamName!";
  $notification['bttnTitle'] = 'View';
  $notification['bttnLink'] = "?q=viewTeam&TID=" . $TID;
  dbAddNotification($notification);

  // notify new owner through email
  $oldOwnerName = dbGetUserName($user->uid);
  $oldOwnerEmail = $user->mail;
  drupal_mail('teams', 'becameOwner', dbGetUserPrimaryEmail($newOwnerUID), variable_get('language_default'), $params = array('teamName'=>$teamName, 'newOwnerName'=>$newOwnerName, 'oldOwnerName'=>$oldOwnerName, 'oldOwnerEmail'=>$oldOwnerEmail, 'TID'=>$TID), $from = NULL, $send = TRUE);

  drupal_goto('showUsersForTeam', array('query'=>array('TID'=>$TID)));

}
  

function kickUserFromTeam($UID, $TID)
{
  global $user;
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  dbKickUserFromTeam($UID, $TID);
  dbRemoveAllUserRoles($UID, $TID);

  $notification = array('UID'=>$UID, 'TID'=>$TID, 'dateTargeted'=>dbDatePHP2SQL(time()), 'dateCreated'=>dbDatePHP2SQL(time()));
  $notification['message'] = 'You have been removed from ' . dbGetTeamName($TID);
  $notification['bttnTitle'] = 'Email Admin';
  $notification['bttnLink'] = "mailto:{$user->mail}";
  dbAddNotification($notification);

  drupal_set_message("User has been removed from your team.");

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('showUsersForTeam', array('query'=>array('TID'=>$TID)));
  }
}

function approveUser($UID, $TID)
{
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  dbApproveUser($UID, $TID);

  $notification = array('UID'=>$UID, 'TID'=>$TID, 'dateCreated'=>dbDatePHP2SQL(time()),'dateTargeted'=>dbDatePHP2SQL(time()));
  $notification['message'] = 'You have just been approved for ' . dbGetTeamName($TID) . '!';
  $notification['bttnTitle'] = 'View Team';
  $notification['bttnLink'] = "?q=viewTeam&TID=$TID";
  dbAddNotification($notification);

  drupal_mail('teams', 'approvedForTeam', dbGetUserPrimaryEmail($UID), variable_get('language_default'), $params = array('teamName' => dbGetTeamName($TID), 'fullName' => dbGetUserName($UID)), $from = NULL, $send = TRUE);

  drupal_set_message("User has been added to your team.");

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewUsersToBeApproved', array('query'=>array('TID'=>$TID)));
  }
}

function rejectUser($UID, $TID)
{
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  dbRejectUser($UID, $TID);

  $notification = array('UID'=>$UID, 'TID'=>$TID, 'dateCreated'=>dbDatePHP2SQL(time()),'dateTargeted'=>dbDatePHP2SQL(time()));
  $notification['message'] = 'You have been rejected from ' . dbGetTeamName($TID) . '.';
  $notification['bttnTitle'] = 'Reapply';
  $notification['bttnLink'] = "?q=applyForTeamForm";
  dbAddNotification($notification);

  module_load_include('inc','mimemail');

  drupal_mail('teams', 'rejectedFromTeam', dbGetUserPrimaryEmail($UID), variable_get('language_default'), $params = array('teamName' => dbGetTeamName($TID), 'fullName' => dbGetUserName($UID)), $from = NULL, $send = TRUE);

  drupal_set_message("User has been rejected from your team.");

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewUsersToBeApproved', array('query'=>array('TID'=>$TID)));
  }
}
?>