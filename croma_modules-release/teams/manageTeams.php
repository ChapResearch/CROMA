<?php

/*
  ---- teams/manageTeams.php ----

  Allows for the managing of team membership(s) on the user's side.

  - Contents -
  switchTeamTab() - paints the block which sits in the menu bar and displays the currently active team. Clicking on it also allows the user to change which team they are operating under (by linking to the switchTeamPage()).
  switchTeamPage() - paints the landing page to allow a user to switch which team they are operating under (given the teams they are currently on). The buttons this page presents link to the function switchTeam(), which will actually change the $_SESSION variable to switch the team the user is operating as.
  switchTeam() - actually switches the team of the current user, given the TID and name of the team to switch to. This function simply sets those variables to the $_SESSION variables keeping track of the "current" team across the site. This is used to determine which team outreaches are added to and whatnot.
  manageUserTeams() - Shows detailed information about the user's statuses for all the teams they are associated with.
  leaveTeam() - function to remove the current user leave the team with the given TID. Usually called as a menu callback.
  rescindTeamApplication() - Cancels the user's application for a certain team.
  makeTeamDefault() - Sets the given team as the default team for the user.
*/

include_once(MODULES_FOLDER.'/helperFunctions.inc');

// switchTeamTab() - paints the block which sits in the menu bar and displays the currently active team. Clicking on it also allows the user to change which team they are operating under (by linking to the switchTeamPage()).
function switchTeamTab()
{
  global $user;

  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $markup = '<br><div style="color:white;"><a href="?q=switchTeam"><button id="switchTeamBttn"><span title="Click this button to switch the team you are operating under">';
  $markup .= dbGetTeamNumber($TID);
  $markup .= '</span></button></a></div>';

  return array('#markup' => $markup);
}

// switchTeamPage() - paints the landing page to allow a user to switch which team they are operating under (given the teams they are currently on). The buttons this page presents link to the function switchTeam(), which will actually change the $_SESSION variable to switch the team the user is operating as.
function switchTeamPage(){
  global $user;
  $UID = $user->uid;

  $teams = dbGetTeamsForUser($UID);

  $markup =  '<table>';
  $markup .= '<h1>' . "Switch The Team You're Operating Under" . '</h1>';
  $markup .= '<tr><td><b>Team Name</b></td>';
  $markup .= '<td><b>Team Number</b></td></tr>';


  foreach($teams as $team) {
    $markup .= '<tr><td>' . '<a href="?q=viewTeam&TID=' . $team['TID'] . '">' . $team['name'] . '</a></td>';
    $markup .= '<td>' . $team['number'] . '</td>';
    $markup .= '<td><a href="?q=switchTeam/' . $team['TID'] . '/' . $team['name'] . '">';
    $markup .= '<button type="button">';
    $markup .= 'Switch To This Team!</button></a>'  . '</td></tr>';
  }

  $markup .= '</table>';

  $array['#markup'] = $markup;

  return $array;
}

// switchTeam() - actually switches the team of the current user, given the TID and name of the team to switch to. This function simply sets those variables to the $_SESSION variables keeping track of the "current" team across the site. This is used to determine which team outreaches are added to and whatnot.
function switchTeam($TID, $teamName)
{
  setCurrentTeam($TID, $teamName);
  $message = "You are now operating under $teamName!";
  $message .= " Click <b><a href=\"?q=makeTeamDefault/$TID/\">here</a></b> to set the team as default.";
  drupal_set_message($message);
  drupal_goto('viewTeam', array('query'=>array('TID'=>$TID)));
}

// manageUserTeams() - Shows detailed information about the user's statuses for all the teams they are associated with.
function manageUserTeams()
{
  global $user;
  $UID = $user->uid;
  $markup = '<table><tr><td><h1>Manage My Teams</h1></td><td style="text-align:right">';
  $markup .= '<a href="?q=teamForm&destination='.current_path().'"><button>Create Team</button></a>';
  $markup .= '<a href="?q=applyForTeamForm&url=manageUserTeams"><button>Apply to Join Team</button></a></td></tr></table>';

  $currentTeams = dbGetTeamsForUser($UID);
  $pendingTeams = dbGetPendingTeams($UID);
  $unapprovedTeams = dbGetUnapprovedTeamsForUser($UID);

  foreach($pendingTeams as &$pendingTeam){
    $pendingTeam['isPending'] = true;
    $pendingTeam['name'] = "<i>{$pendingTeam['name']}</i>";
    $pendingTeam['number'] = "<i>{$pendingTeam['number']}</i>";
  }

  foreach($unapprovedTeams as &$unapprovedTeam){
    $unapprovedTeam['isUnapproved'] = true;
    $unapprovedTeam['name'] = "<i>{$unapprovedTeam['name']}</i>";
    $unapprovedTeam['number'] = "<i>{$unapprovedTeam['number']}</i>";
  }

  $teams = array_merge($currentTeams, $pendingTeams, $unapprovedTeams);

  if (empty($teams)){
    $markup .= '<table class="infoTable">';
    $markup .= '<th></th><tr><td style="text-align:center">';
    $markup .= "You don't have any teams yet! Click the buttons above to create or join one.</td></tr></table>";
  } else {
    $markup .= '<table class="infoTable">';
    $markup .= '<th>Team Name</th>';
    $markup .= '<th>Team Number</th>';
    $markup .= '<th>CROMA Role</th>';
    $markup .= '<th></th>';

    foreach($teams as $team){
      if(isset($team['isUnapproved'])){
	$role = "<i>Team awaiting approval</i>";
      } else if(isset($team['isPending'])){
	$role = "<i>Application Pending</i>";
	$isPending = true;
      } else {
	// beautify the names of the roles
	$role = dbGetRoleForTeam($UID, $team['TID']);
	if(empty($role)){
	  $role = "Member";
	}
	$isPending = false;
      }	

      $markup .= '<tr>';
      $markup .= '<td><a href="?q=viewTeam&TID='.$team['TID'].'">'.$team['name'].'</a></td>';
      $markup .= '<td>' . $team['number'] . '</td>';
      $markup .= '<td>' . $role . '</td>';
      if($role == 'Team Owner'){
	$markup .= '<td><a href="?q=teamForm&TID=' .$team['TID'] . '"><button><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
	$markup .= '<a href="?q=deleteTeamPage&TID=' . $team['TID'] .'"><button><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></td>';
      } else if(isset($team['isPending'])){
	$markup .= '<td><a href="?q=rescindTeamApplication/' . $team['TID'] . '">';
	$markup .= '<button>Rescind Application</button></a>';
      } else if(isset($team['isUnapproved'])){
	$markup .= '<td><a href="?q=teamForm&TID=' .$team['TID'] .'"><button><img class="editIcon" src="/images/icons/editWhite.png"></button></a></td>';
      } else{
	$markup .= "<td><a href=\"?q=leaveTeam/{$team['TID']}\"><button>Leave Team</button></a></td>";
      }
      $markup .= '</tr>';
    }
  }

  $markup .= '</table>';

  $array['#markup'] = $markup;
  return $array;
}

// leaveTeam() - function to remove the current user leave the team with the given TID. Usually called as a menu callback.
function leaveTeam($TID)
{
  global $user;

  dbKickUserFromTeam($user->uid, $TID);
  dbRemoveAllUserRoles($user->uid, $TID);
  dbRemoveUserFromFutureTeamOutreach($user->uid, $TID);
  clearCurrentTeam();
  $notification = array();
  $notification['dateCreated'] = dbDatePHP2SQL(time());
  $notification['dateTargeted'] = dbDatePHP2SQL(time());
  $userName = dbGetUserName($user->uid);
  $teamName = dbGetTeamName($TID);
  $notification['message'] = "$userName has left team $teamName.";
  $notification['bttnTitle'] = "View";
  $notification['bttnLink'] = "?q=viewUser&UID={$user->uid}";
  $notification['TID'] = $TID;
  notifyUsersByRole($notification, 'teamAdmin');
  // notify team owner
  $notification['UID'] = dbGetTeam($TID)['UID']; 
  dbAddNotification($notification);

  drupal_set_message("You have successfully left $teamName.");

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('manageUserTeams');
  }
}

// rescindTeamApplication() - Cancels the user's application for a certain team.
function rescindTeamApplication($TID)
{
  global $user;

  dbRescindTeamApplication($user->uid, $TID);
  $teamName = dbGetTeamName($TID);
  $userName = dbGetUserName($user->uid);

  $notification['message'] = "$userName is no longer applying to $teamName.";
  $notification['dateCreated'] = dbDatePHP2SQL(time());
  $notification['dateTargeted'] = dbDatePHP2SQL(time());
  $notification['TID'] = $TID;

  notifyUsersByRole($notification, 'teamAdmin');

  drupal_set_message("Your application to $teamName has been removed!");


  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('manageUserTeams');
  }
}

// makeTeamDefault() - Sets the given team as the default team for the user.
function makeTeamDefault($TID)
{
  global $user;

  dbSetTeamAsDefaultForUser($user->uid, $TID);

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewTeam', array('query'=>array('TID'=>$TID)));
  }
}

?>