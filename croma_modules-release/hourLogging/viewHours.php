<?php

/*
  ---- hourLogging/viewHours.php ----
  used to show various hours that have been logged (based on various filters)

  - Contents -
  viewHours() - displays a table of hours (taking different parameters to show for users, teams etc.)
*/   

function viewHours()
{
  $params = drupal_get_query_parameters();
  global $user;

  // checks to make sure you are assigned to a team
  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // setting all permissions to a default "false" value
  $canEdit = $canApprove = $myHours = false;

  $markup = '';
  // showing the hours if the UID is set for a user 
  $filterParams = array();
  if(isset($params['UID'])){
    $UID = $params['UID'];
    $myHours = ($UID == $user->uid);
    $filterParams['hourCounting.UID'] = $UID;
    $userName = dbGetUserName($UID);
    $markup = "<table><tr><td><h1>Hours for $userName</h1></td></tr></table>";
  }
  
  // showing the hours if the OID is set for an outreach  
  if(isset($params['OID'])){
    $OID = $params['OID'];
    $TID = getCurrentTeam()['TID'];
    $filterParams['OID'] = $OID;
    $canEdit = canEditHoursForOutreach($OID); // can be set for the entire page
    if(hasPermissionForTeam('manageOutreachTags', $TID)) {
      $canApprove = true; // can be set for the entire page
    }
    $outreachName = dbGetOutreachName($OID);
    $markup = "<table><tr><td><h1>Hours for $outreachName</a></h1></td>";
  }

  if(isset($params['OID']) && isset($params['UID']) && !isset($params['TID'])){
    $markup = "<table><tr><td><h1>Hours contributed to $outreachName by $userName</h1></td></tr></table>";
  }

  // showing the hours needing to be approved for a team if the TID is set
  if(isset($params['TID'])){
    $TID = $params['TID'];
    $filterParams['TID'] = $TID;
    $filterParams['isApproved'] = 0;
    $teamName = dbGetTeamName($TID);
    $markup = "<table><tr><td><h1>Hours to be approved for $teamName</h1></td></tr></table>";
  }

  // if the filters are not empty...
  if(!empty($filterParams)){
    $hoursEntries = dbGetHours($filterParams); // get all the matching "hour" records
    if(isset($OID)){
      $markup .= '<td style="text-align:right">';
      $markup .= "<a href=\"?q=logHours&OID=$OID\"><button>Add Hours</button></a></td></tr></table>";
    }
    $markup .= '<table class="infoTable">'; // starting the table
    if(empty($OID)){
      $markup .= '<th>Event</th>';
    }
    if(!$myHours){
      $markup .= '<th>Person</th>';
    }
    $markup .= '<th>Type</th><th># Hours</th>';
    $markup .= '<th></th>'; // create placeholder column
    
    foreach($hoursEntries as $hours){
      $markup .= '<tr>';
      if(!isset($OID)){ // permissions must be set per hour record
	$canEdit = canEditHours($hours['HID']); // can be set for the entire page
	$canApprove = canApproveHours($hours['HID']); // can be set for the entire page
	if(isset($TID) && !$canApprove){ // if trying to approve hours for the team
	  continue;
	}
	$outreachName = dbGetOutreachName($hours['OID']);
	$markup .= "<td><a href=\"?q=viewOutreach&OID={$hours['OID']}\">$outreachName</a></td>";
      }
      
      // if the hours don't belong the current user, show the name of the person they do belong to
      if(!$myHours){ 
	$markup .= '<td>';
	if ($hours['UID'] != null){
	  $name = dbGetUserName($hours['UID']);
	  $email = dbGetUserPrimaryEmail($hours['UID']);
	  $markup .= "<a href=\"mailto:$email\" target=\"_top\">$name</a>";
	} else {
	  $markup .= '[none]';
	}
	$markup .= '</td>';
      }
      switch($hours['type']){ // switch the type to be more formal
      case 'prep': $formalType = "Preparation"; break;
      case 'atEvent': $formalType = "At Event"; break;
      case 'writeUp': $formalType = "Write Up"; break;
      case 'followUp': $formalType = "Follow Up"; break;
      case 'other': $formalType = "Other"; break;
      }
      $markup .= "<td>$formalType</td>";
      $markup .= "<td>{$hours['numberOfHours']}</td>";

      if($canEdit || $canApprove){
	$markup .= '<td>';
	if($canEdit){ // if user can edit...
	  $markup .= "<a href=\"?q=logHours&HID={$hours['HID']}\">";
	  $markup .= '<button><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
	  $markup .= "<a href=\"?q=deleteHours/{$hours['HID']}\">";
	  $markup .= '<button><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a>';
	}
	if($canApprove && !$hours['isApproved']){ // if user can approve hours and the hours are not approved...
	  $markup .= "<a href=\"?q=approveHours/{$hours['HID']}\">";
	  $markup .= '<button>Approve Hours</button></a>';
	} else if(!$hours['isApproved']){
	  $markup .= '<button disabled>Approve Hours</button>';
	}
	$markup .= '</td>';
      }
      $markup .= '</tr>';
    }

    if(empty($hoursEntries)){
      $markup .= '<tr><td style="text-align:center" colspan="10"><em>[None]</em></td></tr>';
    }

    $markup .= '</table>';
  } else { // no filter params
    drupal_set_message('No filter parameters selected.', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
  return array('#markup'=>$markup);
}

?>