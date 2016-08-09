<?php
/*

Used for display and creation/editing of teams.

- Contents -
viewUserHoursForTeam() - Displays the hours a user has contributed to a team.

*/

function viewHoursForTeam()
{
  global $user;
  $params = drupal_get_query_parameters();

  if(dbGetTeamsForUser($user->uid) == NULL)
    {
      drupal_set_message("You don't have a team assigned.", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
      return;
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
  $markup = '<div style="width:50%;align-text:right;"><h2>Your Approved Hours For Current Team </h2></div>';
  $markup .= '<table>';
  $markup .= '<tr>';
  $markup .= "<th>Name</th>";
  $markup .= "<th>Description</th>";
  $markup .= "<th>Hours</th>";
  $markup .= "</tr>";

  foreach($outreaches as $outreach){
  $markup .= "<tr>";
  $markup .= "<td>" . '<b><a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],15) . '</a>' . "<b></td>";
  $markup .= "<td>" . chopString($outreach["description"],15) ."</td>";
  $markup .= "<td>" . dbGetHoursForUserFromOutreach($UID, $outreach['OID']) . "</tr>";
  $markup .= "</tr>";
  }
      
  $markup .="</table></div>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

function viewEventsForTeam()
{
  global $user;
  $params = drupal_get_query_parameters();

  if(dbGetTeamsForUser($user->uid) == NULL)
    {
      drupal_set_message("You don't have a team assigned.", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
      return;
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

  $outreaches = dbGetOutreachesForUserForTeam($UID, $TID);
  $markup = '<div style="width:50%;align-text:right;"><h2>Outreaches You Have <b>Signed Up</b> For</h2></div>';
  $markup .= '<table>';
  $markup .= '<tr>';
  $markup .= "<th>Name</th>";
  $markup .= "<th>Description</th>";
  $markup .= "<th>Hours</th>";
  $markup .= "</tr>";

  foreach($outreaches as $outreach){
  $markup .= "<tr>";
  $markup .= "<td>" . '<b><a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],15) . '</a>' . "<b></td>";
  $markup .= "<td>" . chopString($outreach["description"],15) ."</td>";
  $markup .= "<td>" . dbGetHoursForUserFromOutreach($UID, $outreach['OID']) . "</tr>";
  $markup .= "</tr>";
  }
      
  $markup .="</table></div>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;

}


?>