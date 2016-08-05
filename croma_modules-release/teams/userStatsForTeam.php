<?php

/*
  ---- teams/userStatsForTeam.php ----
  used for display of user hours and events on a particular team

  - Contents -
  viewUserHoursForTeam() - Displays the hours a user has contributed to a team.
  viewUserEventsForTeam() - Displays the outreaches a user has contributed to a team.
*/

// viewUserHoursForTeam() - page accessed by clicking on "hours" under my stats on [team] on user dashboard
function viewUserHoursForTeam()
{
  global $user;
  $params = drupal_get_query_parameters();

  // if the user doesn't have a team
  if(dbGetTeamsForUser($user->uid) == NULL){
    $link = '?q=manageUserTeams';
    $msg = "You don't have a team assigned! Click <a href=\"$link\">here</a> to manage your teams.";
    drupal_set_message($msg, 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if(isset($params['UID'])){
    $UID = $params['UID'];
  } else {
    $UID = $user->uid;
  }

  if(isset($params['TID'])){
    $TID = $params['TID'];
  } else {
    $currentTeam = getCurrentTeam();
    $TID = $currentTeam['TID'];
  }

  $outreaches = dbGetOutreachHoursForUserForTeam($UID, $TID);
  $teamName = dbGetTeamName($TID);

  // create page header and table
  $markup = "<table><h1>My Hours For Team $teamName</h1></table>";

  $markup .= '<table class="infoTable">';
  $markup .= '<tr>';
  $markup .= '<th colspan="3">Outreach Name</th>';
  $markup .= '<th colspan="3">Description</th>';
  $markup .= '<th colspan="2">Hours</th>';
  $markup .= "</tr>";

  // if the user has hours for the current team
  if(!empty($outreaches)){

    foreach($outreaches as $outreach){
      $markup .= "<tr>";
      $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],30) . '</a>' . "</td>";
      $markup .= '<td colspan="3">' . chopString($outreach["description"],30) ."</td>";
      $markup .= '<td colspan="2">' . dbGetHoursForUserFromOutreach($UID, $outreach['OID']) . "</tr>";
      $markup .= "</tr>";
    }
      
    $markup .="</table>";
  } else {
    // if user hours for team are empty
    $markup .= '<tr><td style="text-align:center" colspan="10"><em>[None]</em></td>';
    $markup .= "</tr>";

  }

  $markup .= "</table>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

// viewUserEventsForTeam() - page accessed by clicking on "outreaches" under my stats on [team] on user dashboard
function viewUserEventsForTeam()
{
  global $user;
  $params = drupal_get_query_parameters();

  // if user does not have a team
  if (dbGetTeamsForUser($user->uid) == NULL){
      drupal_set_message("You don't have a team assigned!", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
    }

  if (isset($params['UID'])){
    $UID = $params['UID'];
  } else {
    $UID = $user->uid;
  }

  if (isset($params['TID'])){
    $TID = $params['TID'];
  } else {
    $currentTeam = getCurrentTeam();
    $TID = $currentTeam['TID'];
  }

  $outreaches = dbGetOutreachesForUserForTeam($UID, $TID);
  $teamName = dbGetTeamName($TID);

  // create page header and table
  $markup = "<table><h1>My Outreaches For Team $teamName</h1></table>";
  $markup .= '<table class="infoTable">';
  $markup .= '<tr>';
  $markup .= '<th colspan="3">Outreach Name</th>';
  $markup .= '<th colspan="3">Description</th>';
  $markup .= '<th colspan="2">Hours</th>';
  $markup .= "</tr>";

  // if user has outreaches for the current team
  if(!empty($outreaches)){

    foreach($outreaches as $outreach){
      $markup .= "<tr>";
      $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],30) . '</a>' . "</td>";
      $markup .= '<td colspan="3">' . chopString($outreach["description"],30) ."</td>";
      $markup .= '<td colspan="2">' . dbGetHoursForUserFromOutreach($UID, $outreach['OID']) . "</tr>";
      $markup .= "</tr>";
    }
      
    $markup .= "</table>";
  } else {    // if the user does not have any outreaches for the current team
    $markup .= '<tr><td style="text-align:center" colspan="10"><em>[None]</em></td>';
    $markup .= "</tr>";
  }

  $markup .= "</table>";


  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

?>