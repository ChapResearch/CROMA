<?php

/*
  ---- outreach/outreachAwaitingApproval.php ----

  used for managing ideas and write-ups that need to be approved.

  - Contents -
  outreachToWriteUp() - converts outreach to write-up status (after event has ended)
  approveIdea() - converts outreach idea into event
  rejectIdea() - deletes outreach idea
  ideasAwaitingApproval() - displays all outreaches with status of idea for a team
  writeUpsAwaitingApproval() - displays all outreaches with status of write-up for a team
*/

// outreachToWriteUp() - converts outreach to write-up status (after event has ended)
function outreachToWriteUp($OID){
  // if the outreach is over
  if (dbIsOutreachOver($OID)){
    $outreach = dbGetOutreach($OID);
    $row = array("status" => "doingWriteUp");
    dbUpdateOutreach($OID, $row);
  }
}

// approveIdea() - Converts outreach idea into event.
function approveIdea($OID){
  if (dbApproveIdea($OID)){
    $outreach = dbGetOutreach($OID);
    $outreachName = dbGetOutreachName($OID);
    $UID = $outreach['UID'];
    $TID = $outreach['TID'];
    // notification to user that their outreach has been approved
    $notification = array(
			  'UID' => $UID,
			  'TID' => $TID,
			  'message' => "$outreachName has been approved.",
			  'dateCreated' => dbDatePHP2SQL(time()),
			  'dateTargeted' => dbDatePHP2SQL(time()),
			  'bttnTitle' => 'View',
			  'bttnLink' => '?q=viewOutreach&OID=' . $OID
			  );
    dbAddNotification($notification);
    drupal_set_message("$outreachName has been approved");
  } else {
    drupal_set_message("An error has occurred.", 'error');
  }

  if (isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('myDashboard');
  }

}

// rejectIdea() - Deletes outreach idea.
function rejectIdea($OID){
  $outreach = dbGetOutreach($OID);
  $outreachName = dbGetOutreachName($OID);
  if (dbRejectIdea($OID)){
    $UID = $outreach['UID'];
    $TID = $outreach['TID'];
    // notification to user that their outreach has been rejected
    $notification = array(
			  'UID' => $UID,
			  'TID' => $TID,
			  'message' => "$outreachName has been rejected.",
			  'dateCreated' => dbDatePHP2SQL(time()),
			  'dateTargeted' => dbDatePHP2SQL(time()),
			  );
    dbAddNotification($notification);
    drupal_set_message("$outreachName has been rejected");
  } else {
    drupal_set_message("An error has occurred.", 'error');
  }

  if (isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('myDashboard');
  }
}

// Displays all outreaches with status of idea for a team.
function ideasAwaitingApproval() 
{
  $currentTeam = getCurrentTeam();

  if ($currentTeam == false){
    drupal_set_message("You don't have a team assigned.", 'error');
    return;
  }

  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  // create header
  $markup = '<h1>Team Moderator Page</h1><br>';
  $markup .= '<h2>Ideas Awaiting Approval</h2>';

  $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
  $markup .= '<th colspan="2">Log Date</th>';
  $markup .= '<th colspan="2">Owner</th>';
  $markup .= '<th colspan="2"></th>';
  $markup .= "</tr>";

  $outreaches = dbGetIdeasForTeam($TID);

  // if the team has ideas
    if (!empty($outreaches)){
      foreach($outreaches as $outreach){

	$TID = $outreach['TID'];
	$team = dbGetTeam($outreach['TID']);
	$rawDate = $outreach['logDate'];
	$rawDate = dbDateSQL2PHP($rawDate);
	$logDate =  date(TIME_FORMAT, $rawDate);
	$OID = $outreach['OID'];
	$owner = dbGetOutreachOwner($OID);

	$markup .= "<tr>";
	$markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID=' . $outreach['OID'] . '">'.chopString($outreach["name"],20) . '</a>' . '</td>';
	$markup .= '<td colspan="2">' . $logDate . '</td>';
	$markup .= '<td colspan="2">' . dbGetUserName($owner) . ' </td>';

	// approve and reject buttons
	if (hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td colspan=\"2\"><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a>";
	  $markup .= "<a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";

	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  
    } else {      // if the team does not have any ideas
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
      $markup .= "</tr>";
    }

    $markup .="</table>";

    $retArray = array();
    $retArray['#markup'] = $markup;
  
    return $retArray;
}

// Displays all outreaches with status of write-up for a team.
function writeUpsAwaitingApproval()
{
  $currentTeam = getCurrentTeam();

  if ($currentTeam == false){
    drupal_set_message("You don't have a team assigned.", 'error');
    return;
  }

  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  // create header
  $markup = '<h2>Write-Ups Awaiting Approval</h2>';

  $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
  $markup .= '<th colspan="2">Log Date</th>';
  $markup .= '<th colspan="2">Owner</th>';
  $markup .= '<th></th>';
  $markup .= "</tr>";

  // "true" indicates to only get write-ups that have been submitted
  $outreaches = dbGetWriteUpsForTeam($TID, true); 

  // if the team has write-ups
  if (!empty($outreaches)){
    $count = 0;
    foreach($outreaches as $outreach){
      $count++;
      $TID = $outreach['TID'];
      $team = dbGetTeam($outreach['TID']);
      $rawDate = $outreach['logDate'];
      $rawDate = dbDateSQL2PHP($rawDate);
      $logDate =  date(TIME_FORMAT, $rawDate);
      $OID = $outreach['OID'];
      $owner = dbGetOutreachOwner($OID);

      $markup .= "<tr>";
      $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID=' . $outreach['OID'] . '">'.chopString($outreach["name"],20) . '</a>' . '</td>';
      $markup .= '<td colspan="2">' . $logDate . '</td>';
      $markup .= '<td colspan="2">' . dbGetUserName($owner) . ' </td>';

      // button to view the write up
      if (hasPermissionForTeam('approveIdeas', $TID)){
	$markup .= '<td><a href="?q=writeupform&approving&OID=' . $OID .'"><button>View</button></a></td>';

      } else {
	$markup .= '<td></td>';
      }
      $markup .= "</tr>";
    }
    // if the team doesn't have any write-ups
  } else{
    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
    $markup .= "</tr>";
  }

  $markup .="</table>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}

?>