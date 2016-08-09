<?php

function adminPage()
{
  $params = drupal_get_query_parameters();
  $markup = '<h3>Manage Teams</h3>';
  $markup .= '<div align="right">';
  if(isset($params['show']) && $params['show'] == 'rejected'){
    $markup .= '<a href="?q=adminPage"><button>';
    $markup .= 'Exclude Rejected Teams';
  } else {
    $markup .= '<a href="?q=adminPage&show=rejected"><button>';
    $markup .= 'Include Rejected Teams';
  }
  $markup .= '</button></a></div>';
  $markup .= '<table><tr><th>TID</th><th>Name</th><th>Number</th>';
  $markup .= '<th>Type</th><th>Location</th><th>Rookie Year</th><th colspan="2">Admin Functions</th></tr>';

  if(isset($params['show']) && $params['show'] == 'rejected'){
    $teams = array_merge(dbGetRejectedTeams(), dbGetTeamsPendingApproval());
  } else{
    $teams = dbGetTeamsPendingApproval();
  }

  foreach($teams as $team){
    $markup .= "<tr><td>{$team['TID']}</td>";
    $markup .= "<td>{$team['name']}</td>";
    $markup .= "<td>{$team['number']}</td>";
    $markup .= "<td>{$team['type']}</td>";
    $markup .= "<td>{$team['city']}, {$team['state']}, {$team['country']}</td>";
    $markup .= "<td>{$team['rookieYear']}</td>";
    $markup .= "<td><a href=\"?q=approveTeam/{$team['TID']}\"><button>Approve</button></a></td>";
    if($team['isApproved'] != '0'){
      $markup .= "<td><a href=\"?q=rejectTeam/{$team['TID']}\"><button>Reject</button></a></td>";
    }
    $markup .= '</tr>';
  }

  $markup .= '</table>';
  return array('#markup'=>$markup);
}

function approveTeam($TID)
{
  $params = drupal_get_query_parameters();
  dbApproveTeam($TID);
  $UID = dbGetOwnerForTeam($TID);
  $teamName = dbGetTeamName($TID);
  drupal_mail('adminFunctions', 'teamApproved', dbGetUserPrimaryEmail($UID), variable_get('language_default'), $params = array('teamName' => $teamName, 'fullName' => dbGetUserName($UID)), $from = NULL, $send = TRUE);
  drupal_set_message('The team has been approved and the team owner has been notified!');

  $notification = array('UID'=>$UID, 'TID'=>$TID, 'dateCreated'=>dbDatePHP2SQL(time()),'dateTargeted'=>dbDatePHP2SQL(time()));
  $notification['message'] = "Your team, team \"$teamName\" has just been approved!";
  $notification['bttnTitle'] = 'View';
  $notification['bttnLink'] = "?q=viewTeam&TID=$TID";
  dbAddNotification($notification);

  if(isset($params['show'])){
    drupal_goto('adminPage', array('query'=>array($params['show'])));
  } else{
    drupal_goto('adminPage');
  }
}

function rejectTeam($TID)
{
  dbRejectTeam($TID);
  $UID = dbGetOwnerForTeam($TID);
  drupal_mail('adminFunctions', 'teamRejected', dbGetUserPrimaryEmail($UID), variable_get('language_default'), $params = array('teamName' => dbGetTeamName($TID), 'fullName' => dbGetUserName($UID)), $from = NULL, $send = TRUE);
  drupal_set_message('The team has been rejected and the team owner has been notified!');

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('adminPage');
  }
}

?>
