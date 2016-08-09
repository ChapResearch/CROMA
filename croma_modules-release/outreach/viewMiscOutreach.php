<?php

/*
  ---- outreach/viewMiscOutreach.php ----

  used for display of outreach events (in misc tables across CROMA)

  - Contents -
  outreachPageHeader() - allows a user to add outreach for their current team
  viewPeopleForEvent() - displays the users associated with an outreach event
  viewOutreachIdeas() - allows a user to view outreaches with the status of idea
  viewOutreachSignedUpFor() - displays outreaches associated with the user
  viewOwnedOutreaches() - displays all outreaches for which the user is the owner
  viewUserUpcomingEvents() - displays all upcoming events that the user has signed up for
*/

// allows a user to add outreach for a team (dropdown changes the current team)
function outreachPageHeader($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $currentTeam = getCurrentTeam();
  $form = array();
  
  // if the user has teams
  if ($currentTeam != false) {
    $TID = $currentTeam['TID'];
    
    $form['fields']['header'] = array(
				      '#markup' => '<table id="outreachPageHeader" style="margin:112px 0px 0px 0px"><tr><td id="addOutreachForTeamText" style="text-align:center; padding:0px"><h2>Add Outreach For '
				      );

    // if the user has multiple teams
    if (dbUserMoreThan1Team($UID)){

      // allows a user to switch teams
      $form['fields']['team'] = array(
				      '#prefix' => '</h2></td><td style="padding:4px">',
				      '#type' => 'select',
				      '#default_value' => $TID,
				      '#options' => dbGetTeamsListForUser($UID),
				      '#chosen'=>true,
				      '#attributes' => array('onChange' => 'document.getElementById("outreachpageheader").submit();'),
				      '#suffix' => '</td></tr>'
				      );
    } else {
      // displays the team number the user is on
      $form['fields']['team'] = array(
				      '#markup' => $currentTeam['number'] . '</h2></td></tr>'
				      );
    }
    
    $form['fields']['submit'] = array(
				      '#type' => 'submit',
				      '#value' => 'Update',
				      // the submit itself is necessary, but now it can be hidden
				      '#attributes' => array(
							     'style' => array('display: none;'),
							     ),
				      );
  }
  
  // button to the outreach form
  $form['button'] = array(
			  '#markup' => '<tr><td colspan="2" style="text-align:right; padding:0px"><a href="?q=outreachForm"><center><div class="help tooltip4"><button type="button" class="largeButton">+ Outreach</button><span id="helptext"; class="helptext tooltiptext4">Click here to add an outreach for your currently active team.</span></div></button></center></a></td></tr></table>'
			  );
  
  return $form; 
}

function outreachPageHeader_submit($form, &$form_state)
{
  $fields = array('team');
  $newTID = getFields($fields, $form_state['values'])['team'];
  setCurrentTeam($newTID);
  $teamNumber = dbGetTeamNumber($newTID);
  drupal_set_message("Now operating under Team $teamNumber!");
  drupal_goto('outreach');
}
// viewPeopleForEvent() - Displays the users associated with an outreach event.
function viewPeopleForEvent()
{
  global $user;
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];

  if (teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page.', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if (dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if (isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);

    // if the outreach is invalid
    if ($outreach == false) { 
      drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
      return;
    }

    // begin header
    $markup = '<div align="left">' . "<br><h1><b> Users Signed Up For: {$outreach['name']}</b></h1></div>";
    
    $ppl = dbGetPplSignedUpForEvent($OID);
    if ($ppl == false){
      $markup .= '<tr><td><h4>No people signed up for outreach: ' . $outreach['name'] . '</h4></td></tr>';
    } else {
      // Begin Displaying Info Body
      $markup .= '<table class="infoTable">';
      $markup .= '<tr><th>Name</th>';
      $markup .= '<th>Email</th>';
      $markup .= '<th>Time Slot(s)</th></tr>';

      foreach($ppl as $UID){
	$profile = dbGetUserProfile($UID); 
	$markup .= '<tr><td><a href="?q=viewUser&UID=' . $profile["UID"] . ' ">';
	$markup .= $profile["firstName"] . " " . $profile["lastName"]. '</a></td>';
	// display the user's email
	$email = dbGetUserPrimaryEmail($UID);
	$markup .= "<td><a href=\"mailto:$email\" target=\"_top\">$email</a></td>";
	$timeSlots = dbGetUserSignUpType($UID, $OID);
	$markup .= '<td>';
	foreach($timeSlots as $timeSlot) {
	  switch($timeSlot) {
	  case 'prep':
	    $markup .= 'Preparation<br>';
	    break;
	  case 'atEvent':
	    $markup .= 'At Event<br>';
	    break;
	  case 'followUp':
	    $markup .= 'Follow Up<br>';
	    break;
	  case 'writeUp':
	    $markup .= 'Write Up<br>';
	    break;
	  case 'owner':
	    $markup .= 'Owner<br>';
	    break;
	  default:
	    break;
	  }
	}
	$markup .= '</td>';
      }
    }

    $markup .= '</table>';
    $retArray = array();
    $retArray['#markup'] = $markup;
    return $retArray;
  } else {
    drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
  }

}

// viewOutreachIdeas() - Allows a user to view outreaches with the status of idea.
function viewOutreachIdeas() 
{ 
  global $user;

  if (dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned.", 'error');
    $TID = 0;
  }

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  if (isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
  }

  // if the user has more than one team
  if (count(dbGetTeamsForUser($UID)) > 1){

    $markup = '<div class="help tooltip2">';
    $markup .= '<h2>My Outreach Ideas</h2>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your ideas that have not yet been approved to become outreaches.';
    $markup .= '</span></div>';

    // allows a user to see the outreach ideas of the team currently being used
    $outreaches = dbGetOutreachIdeas($user->uid,PREVIEW_NUMBER); 

    // begin table
    $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
    $markup .= '<th colspan="3">Log Date</th>';
    $markup .= '<th colspan="2">Team</th>';
    $markup .= '<th colspan="2"></th>';
    $markup .= "</tr>";
  
    // of the user has outreach ideas
    if (!empty($outreaches)){
      foreach($outreaches as $outreach){

	$TID = $outreach['TID'];
	$team = dbGetTeam($outreach['TID']);
	$rawDate = $outreach['logDate'];
	$rawDate = dbDateSQL2PHP($rawDate);
	$logDate =  date(TIME_FORMAT, $rawDate);

	$markup .= "<tr>";
	$markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
	$markup .= '<td colspan="3">' . $logDate . "</td>";
	$markup .= "<td colspan='2'>{$team['number']}</td>";

	// approve and reject idea buttons if the user has permission
	if (hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td colspan=\"2\"><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a>";
	  $markup .= "<a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";

	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  
    } else{
      // display none if the user doesnt have any outreaches
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
    $markup .="</table>";
  
  } else{
    // if the user only has one team

    $markup = '<div class="help tooltip2">';
    $markup .= '<h2>My Outreach Ideas</h2>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your ideas that have not yet been approved to become outreaches.';
    $markup .= '</span></div>';

    // allows a user to see the outreach ideas of the team currently being used
    $outreaches = dbGetOutreachIdeas($user->uid,PREVIEW_NUMBER); 

    $markup .= '<table class="infoTable"><tr><th>Name</th>';
    $markup .= "<th>Log Date</th>";
    $markup .= "<th></th>";
    $markup .= "<th></th>";
    $markup .= "</tr>";
  
    // if the user has outreach ideas
    if (!empty($outreaches)){
      foreach($outreaches as $outreach){

	$TID = $outreach['TID'];
	$rawDate = $outreach['logDate'];
	$rawDate = dbDateSQL2PHP($rawDate);
	$logDate =  date(TIME_FORMAT, $rawDate);

	$markup .= "<tr>";
	$markup .= "<td>" . '<a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
	$markup .= "<td>" . $logDate . "</td>";
	if (hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a></td>";
	  $markup .= "<td><a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";
	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  

    }else{
      // display none if the user doesn't have any outreach ideas
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
    $markup .="</table>";

  }
  
  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}

// viewOutreachSignedUpFor() - Displays outreaches associated with the user.
function viewOutreachSignedUpFor()
{
  global $user;
  $UID = $user->uid;
  if (dbGetTeamsForUser($user->uid) == NULL)
    {
      drupal_set_message("You don't have a team assigned.", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
      return;
    }

  $outreaches = dbGetOutreachForUser($UID);
  $markup = '<div align = "right"><a href="?q=userOwnedOutreaches&UID='.$UID. '"><button>View Outreaches You Own</button></a>';

  // allows a user to sort by outreach they own
  if (!isset($params["owned"])){
    $markup .= '<a href="?q=outreachesSignedUpFor&UID=&owned"><button>Owned Outreaches</button></a>';
  } else {
    $markup .= '<a href="?q=outreachesSignedUpFor"><button>All Outreach</button></a>';
  }

  // begin table
  $markup .= '<table>';
  $markup .= '<tr>';
  $markup .= "<th>Name</th>";
  $markup .= "<th>Description</th>";
  $markup .= "<th>Hours</th>";
  $markup .= "</tr>";

  $markup .= '</table></div><table>';
  
  foreach($outreaches as $outreach) {
    $markup .= '<tr><td style  = "vertical-align: middle;" colspan="2"><a href="?q=viewOutreach&OID=' .$outreach["OID"] . '"</a>'; 
    $markup .= $outreach["name"] . '</td>';
    $markup .= '<td style = "vertical-align: middle;" colspan ="2">' . chopString($outreach["description"], 100) . '</td>';
    $markup .= '</tr>';
  }

  $markup .= '</table>';
  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;

}

// viewOwnedOutreaches() - Displays all outreaches for which the user is the owner.
function viewOwnedOutreaches()
{
  global $user;

  if (dbGetTeamsForUser($user->uid) == NULL) {
    drupal_set_message("You don't have a team assigned.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  $UID = $user->uid;
  $ownedOutreaches = dbGetOwnedOutreachForUser($UID);
  // get the owned outreaches for a user
  $markup = '<div align = "right"><a href="?q=outreachesSignedUpFor&UID='.$UID. '"><button>View Outreaches You Have Signed Up For</button></a></div>';
  $markup .= '<div style="right" . width:30%;"><h2>Outreaches You <b>Own</b></h2>';
  // begin table
  $markup .= '<table>';
  $markup .= '<tr>';
  $markup .= "<th>Name</th>";
  $markup .= "<th>Description</th>";
  $markup .= "<th>Hours</th>";
  $markup .= "</tr>";
  
  foreach($ownedOutreaches as $ownedOutreach){
    $markup .= "<tr>";
    $markup .= "<td>" . '<b><a href="?q=viewOutreach&OID='. $ownedOutreach['OID']. '">'.chopString($ownedOutreach["name"],15) . '</a>' . "<b></td>";
    $markup .= "<td>" . chopString($ownedOutreach["description"],15) ."</td>";
    $markup .= "<td>" . dbGetHoursForUserFromOutreach($UID, $ownedOutreach['OID']) . "</tr>";
    $markup .= "</tr>";
  }
      
  $markup .="</table></div>";

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

// Displays all upcoming events that the user has signed up for.
function viewUserUpcomingEvents()
{
  global $user;
  $UID = $user->uid;

  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  $markup = '<div class="help tooltip2">';
  $markup .= '<h2>My Upcoming Events</h2>';
  $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
  $markup .= 'These are your upcoming events that you own or have signed up for.';
  $markup .= '</span></div>';

  $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
  $markup .= '<th colspan="2">Event Date</th>';

  if (dbUserMoreThan1Team($UID)){  // if the user has more than one team
    $markup .= '<th>Team</th>';
  } else {
    $markup .= '<th></th>';
  }

  $markup .= '<th></th>';
  $markup .= "</tr>";

  $orderParams = 'upcoming';
  $outreaches = dbGetOutreachForUser($user->uid, $orderParams, NUM_UPCOMING_OUTREACHES_SHOWN);

  // if the user has upcoming outreaches
  if (!empty($outreaches)){
    foreach($outreaches as $outreach){
      $OID = $outreach['OID'];

      $TID = $outreach['TID'];
      $team = dbGetTeam($outreach['TID']);
      $eventDate = date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID)));

      // display outreach information
      $markup .= "<tr>";
      $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$OID. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
      $markup .= '<td colspan="2">' . $eventDate . "</td>";
      $markup .= "<td>{$team['number']}</td>";
      $markup .= '<td><a href="?q=signUp&OID=' . $OID . '">';
      $markup .= '<button type="button">Edit Sign Up</button></a></td>';
	  
      $markup .= "</tr>";
    }  
  } else {      // if the user does not have any upcoming events
    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="7"><em>[None]</em></td>';
    $markup .= "</tr>";
  }
  $markup .="</table>";
  
  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}

?>