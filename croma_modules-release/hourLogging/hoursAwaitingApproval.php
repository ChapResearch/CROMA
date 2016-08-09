<?php

/*
  ---- hourLogging/hoursAwaitingApproval.php ----

  used to manage approving and rejecting hours

  - Contents -
  hoursAwaitingApproval() - displays all unapproved user hours for a team
  deleteHours() - used as a menu hook to delete hours
  approveHours() - used as a menu hook to approve hours
*/

// Displays all unapproved user hours for a team.
function hoursAwaitingApproval() 
{
  $currentTeam = getCurrentTeam();
  if ($currentTeam == false){
    drupal_set_message("You don't have a team assigned.", 'error');
    return;
  }

  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  // create header
  $markup = '';
  $markup .= '<h2>Hours Awaiting Approval</h2>';
  $markup .= '<table class="infoTable"><tr>';
  $markup .= '<th colspan="2">Outreach</th>';
  $markup .= '<th colspan="2">User</th>';
  $markup .= '<th colspan="2">Hours</th>';
  $markup .= '<th colspan="2"></th>';
  $markup .= "</tr>";

  $filterParams = array('TID' => $TID, 'isApproved' => false);
  $hours = dbGetHours($filterParams);

  // if the team has unapproved hours
  if (!empty($hours)){
    foreach($hours as $hour){
      $OID = $hour['OID'];
      $outreach = dbGetOutreach($OID);
      $UID = $hour["UID"];
      $numHours = $hour['numberOfHours'];
      $HID = $hour['HID'];

      $markup .= "<tr>";
      $markup .= '<td colspan="2">' . '<a href="?q=viewOutreach&OID=' . $outreach['OID'] . '">'.chopString($outreach["name"],20) . '</a>' . '</td>';
      $markup .= '<td colspan="2">' . dbGetUserName($UID) . ' </td>';
      $markup .= '<td colspan="2"><a href="?q=logHours&HID='.$HID.'">'.$numHours.'</a></td>';
      // approve or reject buttons
      if (canApproveHours($HID)){
	$markup .= "<td colspan=\"2\"><a href=\"?q=approveHours/{$hour['HID']}\"><button>Approve</button></a>";
	$markup .= "<a href=\"?q=deleteHours/$HID\">";
	$markup .= '<button><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></td>';
      } else {
	$markup .= '<td></td><td></td>';
      }
      $markup .= "</tr>";
    }  
  } else {
    // display none if the team has no hours waiting approval
    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
    $markup .= "</tr>";
  }

  $markup .="</table>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}

// function which deletes hours in hourLogging table
function deleteHours($HID) 
{
  dbDeleteHours($HID);
  drupal_set_message('Hours have been deleted.');

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewHours');
  }
}

function approveHours($HID)
{
  dbApproveHours($HID);
  drupal_set_message('Hours have been approved!');

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewHours');
  }
}

?>