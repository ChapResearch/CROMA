<?php

/*
  ---- outreach/viewOutreach.php ----

  Used for display and creation/editing of outreach events.

  - Contents -
  viewUpcomingOutreach() - Displays the outreach sidebar with all team outreach events.
  viewCancelledOutreach() - Displays all cancelled outreach events for a team.
  viewOutreachEvent - Displays all information for a given outreach event.
  viewPeopleForEvent() - Displays the users associated with an outreach event.
  viewOutreachIdeas() - Allows a user to view outreaches with the status of idea.
  approveIdea() - Converts outreach idea into event.
  rejectIdea() - Deletes outreach idea.
  viewOutreachSignedUpFor() - Displays outreaches associated with the user.
  viewOwnedOutreaches() - Displays all outreaches for which the user is the owner.
  viewUpcomingOutreach() - Displays the outreach sidebar with all team outreach events.
*/

function outreachPageHeader($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $team = getCurrentTeam();
  $teams = dbGetTeamsForUser($UID);
  $form = array();
  
  if(!empty($teams)) {
    $TID = $team['TID'];
    
    $form['fields']['header'] = array(
				      '#markup' => '<table id="outreachPageHeader" style="margin:112px 0px 0px 0px"><tr><td id="addOutreachForTeamText" style="text-align:center; padding:0px"><h2>Add Outreach For '
				      );
    if(count($teams) != 1){
      $choices = array();
    
      foreach($teams as $userTeam) {
	$choices[$userTeam['TID']] = $userTeam['number'];
      }

      $form['fields']['team'] = array(
				      '#prefix' => '</h2></td><td style="padding:4px">',
				      '#type' => 'select',
				      '#default_value' => $TID,
				      '#options' => $choices,
				      '#chosen'=>true,
				      '#attributes' => array('onChange' => 'document.getElementById("outreachpageheader").submit();'),
				      '#suffix' => '</td></tr>'
				      );
    } else {
      $form['fields']['team'] = array(
				      '#markup' => $team['number'] . '</h2></td></tr>'
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
  return;
}

function viewUpcomingOutreach() 
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  
  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamName = $team['name'];
  } else {
    $team = getCurrentTeam();
    if(empty($team)){
      //return;
    }
    $TID = $team['TID'];
    $teamName = $team['name'];
  }

  if(teamIsIneligible($TID)) {
    //drupal_set_message('Your team does not have permission to access this page!', 'error');
    $TID = 0;
  }

  $markup = '<h1>My Dashboard</h1>';
  $markup .= '<h3>My Associated Outreach Events</h3>';
  $OIDs = dbGetAssociatedOutreachForUser($UID);
  $markup .= '<div align="left" style="float: left">Sort By: ';

  if(isset($params['sortByDate'])) {
    $markup .= '<a href="?q=myDashboard">Recently Added</a><b> | Upcoming Event</b></div>';
    
    $outreaches = array();

    foreach($OIDs as $OID1) {
      if(dbGetEarliestTimeForOutreach($OID1) !== null) {
	$outreaches[] = dbGetOutreach($OID1);
      }
    }
    unset($OID1);

    // Sort outreaches by start date.
    for($i = 0; $i < count($outreaches) - 1; $i++) { 
      $earliest = $i;
      
      for($j = $i + 1; $j < count($outreaches); $j++) {
	$earlyTime = dbGetEarliestTimeForOutreach($outreaches[$earliest]['OID']);
	$currentTime = dbGetEarliestTimeForOutreach($outreaches[$j]['OID']);
	
	if(strtotime($currentTime) < strtotime($earlyTime)) {
	  $earliest = $j;
	}
      }
      unset($j);
      
      $temp = $outreaches[$i];
      $outreaches[$i] = $outreaches[$earliest];
      $outreaches[$earliest] = $temp;
    }
    unset($i);
  } else {
    $markup .= '<b>Recently Added | </b><a href="?q=myDashboard&sortByDate">Upcoming Event</a></div>';
    $markup .= '<div align="right" style="float:right"><a href="?q=viewUserOutreach"><button>All User Outreach</button></a></div>';

    foreach($OIDs as $OID1) {
      $outreaches[] = dbGetOutreach($OID1);
    }

    unset($OID1);
    orderByValue($outreaches, 'logDate', false);
  }
  
  $markup .= '<table><tr><th>Name</th><th>Event Date</th><th>Owner</th><th></th></tr>';
  if(!teamIsIneligible($TID) || dbGetNumOutreachForUser($UID) == 0){
    if(dbGetNumOutreachForUser($UID) == 0) {
      drupal_set_message("You don't currently have any outreach events!");
      //return;
    }
    $count = 1;

    foreach($outreaches as $outreach) {
      if($count > 5) {
	break;
      }

      if($outreach['cancelled']) {
	continue;
      }

      $count++;

      $OID = $outreach['OID'];
      $markup .= '<tr><td><a href="?q=viewOutreach&OID=' . $OID . '"</a>'; 
      $markup .= $outreach["name"] . '</td>';

      if(null !== dbGetEarliestTimeForOutreach($OID)) {
	$markup .= '<td>' . date(TIME_FORMAT, dbDateSQL2PHP(dbGetEarliestTimeForOutreach($OID))) . '</td>';
      } else {
	$markup .= '<td>[none]</td>';
      }

      $owner = dbGetOutreachOwner($OID);

      if ($owner != -1){
	$markup .= '<td><a href="?q=viewUser&UID=' . $owner . '">' . dbGetUserName($owner) . '</a></td>';
      } else {
	$markup .= '<td>[none]</td>'; // insert placeholder if no outreach owner
      }
    
      if(dbIsUserSignedUp($UID, $OID)){
	if(dbIsOutreachOver($OID)){
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '"><button type="button" disabled>Edit Sign Up</button></a></td></tr>';
	}
	else{
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '"><button type="button">Edit Sign Up</button></a></td></tr>';
	}
      } else{
	if(dbIsOutreachOver($OID)){
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '"><button type="button" disabled>Sign Up</button></a></td></tr>';
	}
	else{
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '"><button type="button">Sign Up</button></a></td></tr>';
	}
      }
    }
  
    unset($count);
  }else {
    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="3"><em>[None]</em></td>';
    $markup .= "</tr>";
  }
  
  $markup .= '</table>';
  return array('#markup' => $markup);
}

/*function viewUserOutreach()
{
  global $user;
  $UID = $user->uid;
  $userName = dbGetUserName($UID);
  $params = drupal_get_query_parameters();
  $currentYear = date("Y");
  $totalFilterHours = 0;
  $search = false;
  $teamsSearch = false;

  if(isset($params['query']) && $params['query'] == 'search'){
    $outreaches = dbSearchOutreach($_SESSION['searchSQL'], $_SESSION['proxyFields']);
    $search = true;
    if(strpos($_SESSION['searchSQL'], 'TID') !== false) {
      $teamsSearch = true;
    }
  } else if(isset($params['owned'])) {
    $outreaches = dbGetOwnedOutreachForUser($UID);
  } else if(isset($params['signedUp'])) {
    $outreaches = dbGetOutreachForUser($UID);
  } else if(isset($params['status'])) {
    if($params['status'] != 'all') {
      $proxyFields = array();
      $outreaches = dbSearchOutreach(generateSearchSQL(array('status' => array($params['status']), 'relation' => 'assocWithUser'), $proxyFields), $proxyFields);
    } else {
      $outreaches = dbGetAllAssociatedOutreachForUser($UID);
    }
  } else if (isset($params['allTeamOutreach'])){
    $specialSearchParams = $_SESSION['searchSQL'];
    $specialSearchParams['TID'] = array('value'=>$params['TID'], 'matchType'=>'exact');
    $outreaches = dbSearchOutreach($_SESSION['searchSQL'], $_SESSION['proxyFields']);
  } else {
    $outreaches = dbGetAllAssociatedOutreachForUser($UID);
  }


  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= '<script src="numberCounting.js"></script>';
  $markup .= '<h1>Outreach</h1><br>';

  if($search) {
    $markup .= '<h2>Search Results (';
    $markup .= empty($outreaches) ? '0' : count($outreaches);
    $markup .= ' matches)</h2>';
  } else {
    $markup .= "<h2>All Outreach for $userName</h2>";
  }

  if(empty($outreaches)) {
    $outreaches = array();
  }

  $totalFilterOutreaches = count($outreaches);
  
  foreach ($outreaches as &$outreach){
    $outreach['hours'] = dbGetHoursForOutreach($outreach['OID']);
    $totalFilterHours += $outreach['hours'];
  }

  unset($outreach);
  $sortParam = isset($params["sort"]) ? $params['sort'] : 'name';
  $statusParam = isset($params['status']) ? $params['status'] : 'all';
  $isAscending = isset($params['isAscending']) ? false : true;
  orderByValue($outreaches, $sortParam, $isAscending); // custom function (see helperFunctions.inc)
  $markup .= '<table style="margin:0px">';
  $markup .= '<tr><td style="padding:0px; text-align:left"><b>Outreaches with Current Filters: </b><span class="countUp">' . $totalFilterOutreaches .'</span></td>';
  $markup .= '<td style="padding:0px; text-align:right" align="right"><b>Hours with Current Filters: </b><span class="countUp">' . $totalFilterHours . '</span></td></tr>';

  $markup .= '<tr><td style="padding:0px" align="left">Sort By';
  if($search) {
    $markup .= ' (Clears Search): ';
    $markup .= sortHeader($sortParam, true, $isAscending, 'Name', 'name', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, true, $isAscending, 'Status', 'status', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, true, $isAscending, 'Hours', 'hours', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, true, $isAscending, 'Event Date', 'eventDate', 'outreach');
  } else {
    $markup .= ': ';
    switch($sortParam) {
    case 'name':
      $markup .= '<b>Name</b><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'status':
      $markup .= '<a href="?q=outreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><b>Status | </b><a href="?q=outreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'hours':
      $markup .= '<a href="?q=outreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><b>Hours | </b><a href="?q=outreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'eventDate':
      $markup .= '<a href="?q=outreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><b>Event Date</b>';
      break;
    default:
      $markup .= '<a href="?q=outreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=outreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    }
  }



  // OLD CODE TO INCLUDE STATUS
  /*
  $markup .= '<tr><td style="padding:0px">Include Status: ';

  if($search) {
    $markup .= '<a href="?q=outreach">All</a><b> | </b><a href="?q=outreach&status=isIdea">Idea</a><b> | </b><a href="?q=outreach&status=isOutreach">Outreach</a><b> | </b><a href="?q=outreach&status=doingWriteUp">Write Up</a></td>';
  } else {
    switch($statusParam) {
    case 'all':
      $markup .= '<b>All</b><b> | </b><a href="?q=outreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=outreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=outreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'isIdea':
      $markup .= '<a href="?q=outreach&sort=' . $sortParam . '">All</a><b> | </b><b>Idea | </b><a href="?q=outreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=outreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'isOutreach':
      $markup .= '<a href="?q=outreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=outreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><b>Outreach | </b><a href="?q=outreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'doingWriteUp':
      $markup .= '<a href="?q=outreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=outreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=outreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><b>Write Up</b>';
      break;
    default:
      $markup .= '<a href="?q=outreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=outreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=outreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=outreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    }
  }
  */
/*$markup .= '</td><td style="padding:0px; text-align:right">';

  if($search) {
    $markup .= '<a href="?q=outreach"><button>All Outreaches</button></a>';
  }

  if(!isset($params['owned'])){
    $markup .= '<a href="?q=outreach&owned"><div class="help tooltip4"><button>Owned</button><span id="helptext"; class="helptext tooltiptext4">Click here to sort by outreach you own.</span></div></a>';
  } else {
    $markup .= '<a href="?q=outreach"><button>All Outreach</button></a>';
  }

  if(!isset($params['signedUp'])){
    $markup .= '<a href="?q=outreach&signedUp"><div class="help tooltip3"><button>Signed Up</button><span id="helptext"; class="helptext tooltiptext3">Click here to sort by outreach you are signed up for.</span></div></a>';
  } else {
    $markup .= '<a href="?q=outreach"><button>All Outreach</button></a>';
  }

  $markup .= '</td></tr></table>';
  $markup .= '<table class="infoTable" style="margin:0px"><tr><th colspan="2">Status</th>';
  $markup .= $teamsSearch ? '<th colspan="2">Team</th>' : '';
  $markup .= '<th colspan="4">Name</th>';
  $markup .= '<th colspan="2">Hours</th>';
  $markup .= '<th colspan="2">Event Date</th>';

  if(empty($outreaches)) {
    $markup .= '<tr><td colspan="11">No outreach found! Click <a href="?q=outreachForm">here</a> to create new outreach!</td></tr></table>';
    return array('#markup' => $markup);
  }

  foreach($outreaches as $outreach) {
    $OID = $outreach['OID'];
    $hours = dbGetHoursForOutreach($OID);
    $status;

    switch($outreach['status']) {
    case 'isOutreach': 
      $status = '<span title="Outreach Event"><img class="eventIndicatorIcon" src="/images/icons/outreachBlue.png"></span>'; 
      break;
    case 'isIdea': 
      $status = '<span title="Idea"><img class="eventIndicatorIcon" src="/images/icons/ideaBlue.png"></span>'; 
      break;
    case 'doingWriteUp': 
      $status = '<span title="Write Up"><img class="eventIndicatorIcon" src="/images/icons/writeUpBlue.png"></span>
'; 
      break;
    case 'locked': 
      $status = '<span title="Locked Event"><img class="eventIndicatorIcon" src="/images/icons/lockedBlue.png"></span>'; 
      break;
    }  

    $markup .= '<tr><td colspan="2" style="padding: 0px 0px 0px 14px;">';
    $markup .= "$status</td>";
    if($teamsSearch) {
      $markup .= '<td colspan="2"><a href="?q=viewTeam&TID=' . $outreach['TID'] . '">' . dbGetTeamNumber($outreach['TID']) . '</a></td>';
    }
    $markup .= '<td colspan="4"><a href="?q=viewOutreach&OID=' .$OID . '">' . chopString($outreach['name'], 15) . '</a></td>';
    $markup .= '<td colspan="2">' . $hours . '</td>';


    if(null !== dbGetEarliestTimeForOutreach($OID)) {
      $markup .= '<td colspan="2">' . date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID))) . '</td>';
    } else {
      $markup .= '<td colspan="2">[none]</td>';
    }
  }
  
  $markup .= '</table>';
  return array('#markup' => $markup);
}*/

// viewCancelledOutreach() - Displays all cancelled outreach events for a team.
function viewCancelledOutreach() {
  global $user;
  $UID = $user->uid;
  
  $team = getCurrentTeam();
  $TID = $team['TID'];

  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }
  
  $events = dbGetCancelledOutreach($TID);
  $markup = "<table>";
  $markup .= "<h1>Cancelled Events For " . $team['number'] . "</h1>";
  $markup .= "<tr>";
  $markup .= "<th>Name</th>";
  $markup .= "<th>Event Date</th>";
  $markup .= "</tr>";
  
  if($events != NULL){
    foreach($events as $event) {
      $rawDate = $event['logDate'];
      $rawDate = dbDateSQL2PHP($rawDate);
      $date =  date(TIME_FORMAT, $rawDate);
      $OID = $event['OID'];
      $markup .= '<tr>';
      $markup .= '<td>' .'<div align="left">' . '<a href="?q=viewOutreach&OID='.$event['OID'].'">'.chopString($event['name'],15) . '</a>' .  '</div></td>';
      $markup .= "<td>$date</td></tr>";
    }
  }
  $markup .= '</tr></table>'; 
  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

// viewOutreachEvent - Displays all information for a given outreach event.
function viewOutreachEvent() {
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
    if($outreach == false) {
      drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
      return;
    }

    $TID = $outreach['TID'];
    if(!isMyTeam($TID)){
      drupal_set_message('You do not have permission to access this page!', 'error');
      return;
    }      

    if($outreach['status'] == "isOutreach") {
      outreachToWriteUp($OID);
    }
    
    // determine if the user can physically sign up
    $canSignUp = (!dbIsOutreachOver($OID) 
		  && ($outreach['status'] == 'isOutreach' || $outreach['status'] == 'doingWriteUp')); 

    $markup = '';
    $markup .= '<div style="float:left; width:38%">';
    $markup .= '<table style="margin:0px 0px 10px 0px;"><tr>';
    $markup .= '<td style="padding:0px 14px 10px 14px;"><div align="left"><h2 style="margin:0px 0px 7px 0px;"><b>';
    $markup .= "{$outreach['name']}";
    $markup .= '</b></h2></div></td></tr>';

    $markup .= '<tr><td>';    


    switch($outreach['status']) {
    case 'isOutreach': $markup .= '<span title="Outreach Event"><img class="eventIndicatorIcon" src="/images/icons/outreachBlue.png"></span>'; break;
    case 'isIdea': $markup .= '<span title="Idea"><img class="eventIndicatorIcon" src="/images/icons/ideaBlue.png"></span>'; break;
    case 'doingWriteUp': $markup .= '<span title="Write Up"><img class="eventIndicatorIcon" src="/images/icons/writeUpBlue.png"></span>
'; break;
    case 'locked': $markup .= '<span title="Locked Event"><img class="eventIndicatorIcon" src="/images/icons/lockedBlue.png"></span>'; break;
    }  

    $markup .= $outreach['isPublic'] ? '<span title="Public"><img class="eventPrivacyIcon" src="/images/icons/publicBlue.png"></span>' : '<span title="Private"><img class="eventPrivacyIcon" src="/images/icons/privateBlue.png"></span>';
    
    $markup .= $outreach['cancelled'] ? '<span title="Event Cancelled"><img class="eventCancelledIcon" src="/images/icons/cancelledRed.png"':'';


    $markup .= '</td></tr></table>';
    $markup .= '<table id="photoAndEdit"><tr><td style="padding:0px;">';  

    if(!isMyOutreach($OID) && !hasPermissionForTeam('editAnyOutreach',getCurrentTeam()['TID'])){
      $markup .= '<div align="right">';
      $markup .= '<span title="Edit Photo"><button type="button" disabled><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></span>';
      $markup .='</div>';
    } else {
      $markup .= '<div align="right">';
      $markup .= '<a href= "?q=editThumbnail';
      $markup .= '&OID='. $OID . '&FID=' . $outreach['FID'] . '">';
      $markup .= '<span title="Edit Photo"><button type="button"><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></a></span>';
      $markup .='</div>';
    }

    $markup .= '</td></tr><tr><td style="padding:0px;">';
    if(!empty($outreach['FID'])) {
      $FID = dbGetOutreachThumbnail($OID);
      $url = generateURL($FID);
      $markup .= '<div align="center"><img src="' . $url . '" style="max-width:150px; width:auto; height:auto; padding: 5px 0px 5px 0px">';
    } else {
      $markup .= '<div align="center"><img src="/images/defaultPics/team.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">';
    }
    
    $markup .= '</div></td></tr></table></div>';
    $markup .= '<div align="right">';

    if($outreach['status'] == 'doingWriteUp' && !$outreach['isWriteUpSubmitted']){
      $markup .= '<a href="?q=writeupform&OID=' .$outreach['OID']. '"><button>Write Up</button></a>';
    } else if($outreach['isWriteUpSubmitted'] && hasPermissionForTeam('approveIdeas', $TID) && $outreach['status'] == 'doingWriteUp'){
      $markup .= '<a href="?q=writeupform&OID=' .$outreach['OID']. '&approving"><button>Approve Write Up</button></a>';
    } else {}

    if($outreach['status'] == 'isIdea' && hasPermissionForTeam('approveIdeas', $TID)){
      $markup .= '<a href="?q=approveIdea/' .$outreach['OID'] . '/'. $TID. '"><button>Approve</button></a>';
      $markup .= '<a href="?q=rejectIdea/' .$outreach['OID'] . '/'. $TID. '"><button>Reject</button></a>';
    }

    if ($outreach['UID'] == $user->uid){
      $markup .= '<a href="?q=manageNotifications&OID='.$outreach['OID'].'"><div class="help tooltip2"><button>Notifications</button><span id="helptext"; class="helptext tooltiptext2">Click here to add or view notifications for this outreach.</span></div></a>';
    } else {
      $markup .= '<a href="?q=manageNotifications&OID='.$outreach['OID'].'"><div class="help tooltip2"><button type="button" disabled>Notifications</button><span id="helptext"; class="helptext tooltiptext2">Click here to add or view notifications for this outreach.</span></div></a>';

    }

    if(!dbIsOutreachCancelled($OID)){
      if(dbIsUserSignedUp($UID, $OID)){
	if(dbIsOutreachOver($OID)){
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Edit Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot edit your sign up for this event because it is already over.</span></div></a>';
	}
	else{
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button">Edit Sign Up</button><span id="helptext"; class="helptext tooltiptext4">Click here to edit your sign up for this event.</span></div></a>';
	}
      } else{
	if(dbIsOutreachOver($OID) || $outreach['status'] == 'isIdea'){
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot sign up for this event because it is already over.</span></div></a>';
	}
	else{
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button">Sign Up</button><span id="helptext"; class="helptext tooltiptext4">Click here to sign up for this event.</span></div></a>';
	}
      }
    }else{
      $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot sign up for this event because it is cancelled.</span></div></a>';
    }

      if(!dbIsOutreachCancelled($OID)){
      $markup .= '<a href= "?q=viewHours';
      $markup .= '&OID='. $OID . '">';
      $markup .= '<div class="help tooltip1"><button type="button" ';
      $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
      $markup .='>Hours</button><span id="helptext"; class="helptext tooltiptext1">Click here to add or view hours for this outreach.</span></div></a>';
    } else {
      $markup .= '<a href= "?q=viewHours';
      $markup .= '&OID='. $OID . '">';
      $markup .= '<div class="help tooltip1"><button type="button" disabled';
      $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
      $markup .='>Hours</button><span id="helptext"; class="helptext tooltiptext1">Click here to add or view hours for this outreach.</span></div></a>';
    }

    $markup .= '<a href="?q=viewMedia';
    $markup .= '&OID='. $OID . '">';
    $markup .= '<button type="button"';
    $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
    $markup .= '>Media</button></a>';

    // This became an icon
    /*    $markup .= '<a href= "?q=editThumbnail';
	  $markup .= '&OID='. $OID . '&FID=' . $FID . '">';
	  $markup .= '<span title="Edit Photo"><button type="button">Edit Photo</button></a></span>';*/


    if($outreach['status'] == 'locked' && (!isMyOutreach($OID) && !hasPermissionForTeam('editAnyOutreach',getCurrentTeam()['TID']))){
      $markup .= '<button type="button" disabled><img class="editIcon" src="/images/icons/editWhite.png"></button>';
    } else {
      $markup .= '<a href= "?q=outreachForm';
      $markup .= '&OID='. $OID . '">';
      $markup .= '<button type="button"><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
    }
    $markup .='</div>';

    /* I think this is all icon code that you can delete now (double check)

       $markup .= '<div style="width:40%; float:right"; align="right">';    

       switch($outreach['status']) {
       case 'isOutreach': $markup .= '<span title="Outreach Event"><img src="/images/icons/outreachBlue.png" style="max-width:25px; width:auto; height:auto; padding: 3px 0px 0px 0px"></span>'; break;
       case 'isIdea': $markup .= '<span title="Idea"><img src="/images/icons/ideaBlue.png" style="max-width:25px; width:auto; height:auto; padding: 3px 0px 0px 0px"></span>'; break;
       case 'doingWriteUp': $markup .= '<span title="Write Up"><img src="/images/icons/writeUpBlue.png" style="max-width:25px; width:auto; height:auto; padding: 3px 0px 0px 0px"></span>
       '; break;
       case 'locked': $markup .= '<span title="Locked Event"><img src="/images/icons/lockedBlue.png" style="max-width:25px; width:auto; height:auto; padding: 3px 0px 0px 0px"></span>'; break;
       }  

       $markup .= $outreach['isPublic'] ? '<span title="Public"><img src="/images/icons/publicBlue.png" style="max-width:30px; width:auto; height:auto; padding: 3px 0px 0px 0px"></span>' : '<span title="Private"><img src="/images/icons/privateBlue.png" style="max-width:30px; width:auto; height:auto; padding: 0px 0px 0px 0px"></span>';
    
       $markup .= $outreach['cancelled'] ? '<span title="Event Cancelled"><img src="/images/icons/cancelledRed.png" style="max-width:65px; width:auto; height:auto; padding: 0px 0px 0px 0px">':'';

       $markup .='</div>';

       I think this is where the icon code stops (double check before you delete)*/


    $markup .= '<div style="width:60%; float:right; padding-left:10px">';

    $hasPointOfContact = false;
    if (!(empty($outreach['co_organization']) && empty($outreach['co_firstName']) && empty($outreach['co_email']) && empty($outreach['co_phoneNumber']))){
      $hasPointOfContact = true;
    }

    // Account for cases where no info is present
    if($outreach['description'] == null) $outreach['description'] = '[none]';
    if($outreach['type'] == null || $outreach['type'] == '' ) $outreach['type'] = '[none]';
    if($outreach['status'] == null) $outreach['status'] = '[none]';
    if($outreach['co_organization'] == null) $outreach['co_organization'] = '[none]';
    if($outreach['co_position'] == null) $outreach['co_position'] = '[none]';
    if($outreach['co_firstName'] == null) $outreach['co_firstName'] = '[none]';
    if($outreach['co_email'] == null) $outreach['co_email'] = '[none]';
    if($outreach['co_phoneNumber'] == null) $outreach['co_phoneNumber'] = '[none]';
    if($outreach['city'] == null) $outreach['city'] = '[none]';
    if($outreach['state'] == null) $outreach['state'] = '[none]';
    if($outreach['address'] == null) $outreach['address'] = '[none]';
    if($outreach['country'] == null) $outreach['country'] = '[none]';
    if($outreach['totalAttendance'] == null) $outreach['totalAttendance'] = 0;
    if($outreach['testimonial'] == null) $outreach['testimonial'] = '[none]';

    $team = dbGetTeam($outreach['TID']);
    
    // Begin Displaying Info Body

    $markup .= '<table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><h3><b><u>General<u></b></h3></td></tr>';
    $owner = dbGetOutreachOwner($OID);
    $markup .= "<tr><td colspan='3'><b>Owner: </b>" . dbGetUserName($owner) . "</a></td>";
    $markup .= "<td colspan='3'><b>Team: </b>{$team['number']}</td></tr>";
    
    $markup .= '<tr><td colspan="3"><b>Tags: </b>';
    $tags = dbGetTagsForOutreach($OID);
    if(!empty($tags)){
      dpm($tags);
      $first = true;
      $length = count($tags);
      $i = 1;
      foreach($tags as $OTID => $tagName){
	$markup .= '<a href="?q=outreach&tag=' . $OTID . '">' . $tagName . '</a>';
	if($i < $length){
	  $markup .= ', ';
	}
	$i++;

      }
    } else { // if there aren't any tags
      $markup .= '[none]';
    }
    $markup .= '</td></tr>';

    $times = dbGetTimesForOutreach($OID);


    if($outreach['status'] != 'isIdea'){
      if(!empty($times)){
	foreach($times as $time){
	  $startTime = date(TIME_FORMAT, dbDateSQL2PHP($time['startTime']));
	  $endTime = date(TIME_FORMAT, dbDateSQL2PHP($time['endTime']));
	  $markup .= '<tr><td colspan="3"><b>Start Date: </b>' . $startTime . '</td>';
	  $markup .= '<td colspan="3"><b>End Date: </b>' . $endTime . '</td></tr>';
	}
      }
    }


    $markup .= '<tr><td colspan="5" style="word-break:break-word"><b>Description: </b>';

    $markup .= wordwrap($outreach['description'], 70, "<br />\n");

    //Old code for view more on outreach description

    /*    $tooLong = strlen($outreach['description']) > 50;
	  if (!isset($params['viewMore']) && $tooLong){
	  $markup .= strip_tags(chopString($outreach["description"], 50), ALLOWED_TAGS);
	  } else {
	  $markup .= strip_tags($outreach["description"], ALLOWED_TAGS);
	  }
	  $markup .= '</td>';

	  if($tooLong){ // if the string was long enough to be cut off
	  $markup .= "<td><a href=\"?q=viewOutreach&OID=$OID";
	  if(isset($params['viewMore'])){
	  $markup .= '"><button>Hide</button><a></td>';
	  } else {
	  $markup .= '&viewMore">';
	  $markup .= '<button>View More</button><a></td>';
	  }
	  }

    */

    $markup .= '</td></tr>';

    if ($hasPointOfContact){
      $markup .= '<tr><td><h3><b><u>Contact Info<u></b></h3></td></tr>';

      $markup .= '<tr><td colspan="3"><b>Host Organization: </b>';
      $markup .= strip_tags($outreach['co_organization'], ALLOWED_TAGS) . '</td>';
      $markup .= '<td colspan="3"><b>Contact Name: </b>';
      $markup .= strip_tags($outreach['co_firstName'].' '.$outreach['co_lastName'], ALLOWED_TAGS) . '</td></tr>';
      $markup .= '<tr><td colspan="3"><b>Contact Email: </b>'.strip_tags($outreach['co_email'], ALLOWED_TAGS).'</td>';
      $phoneNumber = dbFormatPhoneNumber($outreach['co_phoneNumber']);
      $markup .= '<td colspan="3"><b>Contact Number: </b>' . $phoneNumber . '</td></tr>';
      $markup .= '<tr><td colspan="6"><b>Address: </b>'.strip_tags($outreach['address'], ALLOWED_TAGS).', '.strip_tags($outreach['city'], ALLOWED_TAGS).', '.strip_tags($outreach['state'], ALLOWED_TAGS).'</td></tr>';
      //      $markup .= '<tr><td colspan="6"><b>Comments: </b>' . wordwrap($outreach['testimonial'], 70, "<br />\n") . '</td></tr>';
      $markup .= '</tr>';
      
    }

    $markup .= '<tr><td><h3><b><u>Statistics<u></b></h3></td></tr>';
    //    $markup .= '<tr><td colspan="3"><b>Total Event Attendance:</b> ' .$outreach['totalAttendance'] . '</td>';
    $markup .= '<tr>';
    
    if($outreach['status'] != 'isIdea'){
      $numPpl = dbGetNumPplSignedUpForEvent($OID);
      $markup .= '<td colspan="3"><b>';
      if($numPpl != 0){ // only show the link if people are signed up
	$markup .= '<a href="?q=outreachList&OID='. $OID .'"target="_blank">';
      }
      $markup .= 'People Signed Up: </b>';
      if($numPpl != 0){$markup .= '</a>';} // end the link
      $markup .= $numPpl . '</td>';

      $markup .= '<td colspan="3"><b>Total Hours: </b><a href="?q=viewHours&OID='.$OID.'">' . dbGetHoursForOutreach($OID) . '</a></td></tr>';
    }
    
    $markup .= '</table></div>';
    if($outreach['isWriteUpApproved'] && $outreach['status'] == 'locked'){
      $writeUp = empty($outreach["writeUp"])?'[None]':$outreach["writeUp"];
      $totalAttendance = empty($outreach["totalAttendance"])?'[Not Filled Out]':$outreach["totalAttendance"];
      $testimonial = empty($outreach["testimonial"])?'[None]':$outreach["testimonial"];
      $markup .= '<div style="float:left; width:38%;"><table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><h3><b><u>Write Up<u></b></h3></td>';
      $markup .= '<td><a href="?q=writeupform&OID=' .$outreach['OID']. '&approved"><button> View</button></a></td></tr>';
      $markup .= '<tr><td><b>Write Up:</b></td></tr>';
      $markup .= '<tr><td>'. $writeUp .'</td></tr>';
      $markup .= '<tr><td><b>Total Attendance:</b></td></tr>';
      $markup .= '<tr><td>'. $totalAttendance .'</td></tr>';
      $markup .= '<tr><td><b>Testimonials/Comments:</b></td></tr>';
      $markup .= '<tr><td>'. $testimonial .'</td></tr>';
      $markup .= '</table></div>';
    }

    $retArray = array();
    $retArray['#markup'] = $markup;
    return $retArray;
  } else {
    drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
  }
}


// viewPeopleForEvent() - Displays the users associated with an outreach event.
function viewPeopleForEvent()
{
  global $user;
  $params = drupal_get_query_parameters();
  $TID = getCurrentTeam()['TID'];

  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);

    if($outreach['name'] == null) { // implies rest of outreach is empty
      drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
      return;
    }

    $markup = '<div align="left">' . "<br><h1><b> Users Signed Up For: {$outreach['name']}</b></h1></div>";
    
    $ppl = dbGetPplSignedUpForEvent($OID);

    if($ppl == -1){
      $markup .= '<tr><td><h4>No people signed up for outreach: ' . $outreach['name'] . '!</h4></td></tr>';
    }

    else{
      // Begin Displaying Info Body
      $markup .= '<table class="infoTable">';
      $markup .= '<tr><th>Name</th>';
      $markup .= '<th>Email</th></tr>';

      foreach($ppl as $UID){
	$prof = dbGetUserProfile($UID); //$prof is short for "profile"
	$markup .= '<tr><td><a href="?q=viewUser&UID=' . $prof["UID"] . ' ">';
	$markup .= $prof["firstName"] . " " . $prof["lastName"]. '</a></td>';
	$email = dbGetUserPrimaryEmail($UID);
	$markup .= "<td><a href=\"mailto:$email\" target=\"_top\">$email</a></td></tr>";
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

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
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

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
  }

  if(count(dbGetTeamsForUser($UID)) > 1){

    $markup = '<div class="help tooltip2">';

    $markup .= '<h2>My Outreach Ideas</h2>';

    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your ideas that have not yet been approved to become outreaches.';

    $markup .= '</span></div>';

    $outreaches = dbGetOutreachIdeas($user->uid,PREVIEW_NUMBER); // allows a user to see the outreach ideas of the team currently being used

    $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
    $markup .= '<th colspan="3">Log Date</th>';
    $markup .= '<th colspan="2">Team</th>';
    $markup .= '<th colspan="2"></th>';
    $markup .= "</tr>";
  
    if(!empty($outreaches)){
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

	if(hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td colspan=\"2\"><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a>";
	  $markup .= "<a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";

	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  
    } else{

      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
    $markup .="</table>";
  
  }
  
  else{

    $markup = '<div class="help tooltip2">';

    $markup .= '<h2>My Outreach Ideas</h2>';

    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your ideas that have not yet been approved to become outreaches.';

    $markup .= '</span></div>';

    $outreaches = dbGetOutreachIdeas($user->uid,PREVIEW_NUMBER); // allows a user to see the outreach ideas of the team currently being used

    $markup .= '<table class="infoTable"><tr><th>Name</th>';
    $markup .= "<th>Log Date</th>";
    $markup .= "<th></th>";
    $markup .= "<th></th>";
    $markup .= "</tr>";
  
    if(!empty($outreaches)){
      foreach($outreaches as $outreach){

	$TID = $outreach['TID'];
	$rawDate = $outreach['logDate'];
	$rawDate = dbDateSQL2PHP($rawDate);
	$logDate =  date(TIME_FORMAT, $rawDate);

	$markup .= "<tr>";
	$markup .= "<td>" . '<a href="?q=viewOutreach&OID='.$outreach['OID']. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
	$markup .= "<td>" . $logDate . "</td>";
	if(hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a></td>";
	  $markup .= "<td><a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";
	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  

    }else{
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

// outreachToWriteUp() - converts outreach to write-up status (after one week of event end date)
function outreachToWriteUp($OID){
  if(dbIsOutreachOver($OID)){
    $outreach = dbGetOutreach($OID);
    $row = array("status" => "doingWriteUp");
    dbUpdateOutreach($OID, $row);
  }
}

// approveIdea() - Converts outreach idea into event.
function approveIdea($OID){
  if(dbApproveIdea($OID)){
    $outreach = dbGetOutreach($OID);
    $outreachName = dbGetOutreachName($OID);
    $UID = $outreach['UID'];
    $TID = $outreach['TID'];
    $notification = array(
			  'UID' => $UID,
			  'TID' => $TID,
			  'message' => "$outreachName has been approved!",
			  'dateCreated' => date(DEFAULT_TIME_FORMAT, time()),
			  'dateTargeted' => date(DEFAULT_TIME_FORMAT, time()),
			  'bttnTitle' => 'View',
			  'bttnLink' => '?q=viewOutreach&OID=' . $OID
			  );
    dbAddNotification($notification);
    drupal_set_message("$outreachName has been approved");
  } else {
    drupal_set_message("An error has occurred.", 'error');
  }

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('myDashboard');
  }

}

function rejectIdea($OID){
  $outreach = dbGetOutreach($OID);
  $outreachName = dbGetOutreachName($OID);
  if(dbRejectIdea($OID)){
    $UID = $outreach['UID'];
    $TID = $outreach['TID'];
    $notification = array(
			  'UID' => $UID,
			  'TID' => $TID,
			  'message' => "$outreachName has been rejected!",
			  'dateCreated' => date(DEFAULT_TIME_FORMAT, time()),
			  'dateTargeted' => date(DEFAULT_TIME_FORMAT, time()),
			  );
    dbAddNotification($notification);
    drupal_set_message("$outreachName has been rejected");
  } else {
    drupal_set_message("An error has occurred.", 'error');
  }

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('myDashboard');
  }

}

// viewOutreachSignedUpFor() - Displays outreaches associated with the user.
function viewOutreachSignedUpFor()
{
  global $user;
  $UID = $user->uid;
  if(dbGetTeamsForUser($user->uid) == NULL)
    {
      drupal_set_message("You don't have a team assigned!", 'error');
      drupal_goto($_SERVER['HTTP_REFERER']);
      return;
    }

  $outreaches = dbGetOutreachForUser($UID);
  $markup = '<div align = "right"><a href="?q=userOwnedOutreaches&UID='.$UID. '"><button>View Outreaches You Own</button></a>';

  if(!isset($params["owned"])){
    $markup .= '<a href="?q=outreachesSignedUpFor&UID=&owned"><button>Owned Outreaches</button></a>';
  } else {
    $markup .= '<a href="?q=outreachesSignedUpFor"><button>All Outreach</button></a>';
  }

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

  if(dbGetTeamsForUser($user->uid) == NULL) {
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  $UID = $user->uid;
  $ownedOutreaches = dbGetOwnedOutreachForUser($UID);
  $markup = '<div align = "right"><a href="?q=outreachesSignedUpFor&UID='.$UID. '"><button>View Outreaches You Have Signed Up For</button></a></div>';
  $markup .= '<div style="right" . width:30%;"><h2>Outreaches You <b>Own</b></h2>';
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

function viewUserUpcomingEvents()
{
  global $user;

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
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

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
  }

  if(count(dbGetTeamsForUser($UID)) > 1){

    $markup = '<div class="help tooltip2">';

    $markup .= '<h2>My Upcoming Events</h2>';

    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your upcoming events that you have signed up for.';

    $markup .= '</span></div>';

    $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
    $markup .= '<th colspan="2">Event Date</th>';
    $markup .= '<th colspan="2">Team</th>';
    $markup .= '<th></th>';
    $markup .= "</tr>";

    $outreaches = dbGetOutreachForUser($user->uid);

    $temp = array();

    foreach($outreaches as $outreach) {
      if(dbGetEarliestTimeForFutureOutreach($outreach['OID']) !== null) {
	$temp[] = $outreach;
      }
    }

    unset($outreach);
    $outreaches = $temp;
    unset($temp);

    // Sort outreaches by start date.
    for($i = 0; $i < count($outreaches) - 1; $i++) { 
      $earliest = $i;
      
      for($j = $i + 1; $j < count($outreaches); $j++) {
	$earlyTime = dbGetEarliestTimeForOutreach($outreaches[$earliest]['OID']);
	$currentTime = dbGetEarliestTimeForOutreach($outreaches[$j]['OID']);
	
	if(strtotime($currentTime) < strtotime($earlyTime)) {
	  $earliest = $j;
	}
      }
  
      unset($j);
      
      $temp = $outreaches[$i];
      $outreaches[$i] = $outreaches[$earliest];
      $outreaches[$earliest] = $temp;
    }
    
    unset($i);

    if(!empty($outreaches)){
      $count = 1;
      foreach($outreaches as $outreach){
	$OID = $outreach['OID'];
	if($count > 5) {
	  break;
	}
	if(dbIsOutreachOver($OID) ||  dbIsOutreachCancelled($OID)){
	  // DO NOTHING
	} else {
	  $count++;
	  $TID = $outreach['TID'];
	  $team = dbGetTeam($outreach['TID']);
	  $eventDate = date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID)));

	  $markup .= "<tr>";
	  $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$OID. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
	  $markup .= '<td colspan="2">' . $eventDate . "</td>";
	  $markup .= "<td colspan='2'>{$team['number']}</td>";
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '">';
	  $markup .= '<button type="button">Edit Sign Up</button></a></td>';
	  
	  $markup .= "</tr>";
	}
      }  

    } else{

      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
    $markup .="</table>";
  
  }
  
  else{

    $markup = '<div class="help tooltip2">';

    $markup .= '<h2>My Upcoming Events</h2>';

    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'These are your upcoming events that you have signed up for.';

    $markup .= '</span></div>';

    $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
    $markup .= '<th colspan="2">Event Date</th>';
    $markup .= '<th></th>';
    $markup .= "</tr>";

    $outreaches = dbGetOutreachForUser($user->uid);

    $temp = array();

    foreach($outreaches as $outreach) {
      if(dbGetEarliestTimeForFutureOutreach($outreach['OID']) !== null) {
	$temp[] = $outreach;
      }
    }

    unset($outreach);
    $outreaches = $temp;
    unset($temp);

    // Sort outreaches by start date.
    for($i = 0; $i < count($outreaches) - 1; $i++) { 
      $earliest = $i;
      
      for($j = $i + 1; $j < count($outreaches); $j++) {
	$earlyTime = dbGetEarliestTimeForOutreach($outreaches[$earliest]['OID']);
	$currentTime = dbGetEarliestTimeForOutreach($outreaches[$j]['OID']);
	
	if(strtotime($currentTime) < strtotime($earlyTime)) {
	  $earliest = $j;
	}
      }
  
      unset($j);
      
      $temp = $outreaches[$i];
      $outreaches[$i] = $outreaches[$earliest];
      $outreaches[$earliest] = $temp;
    }
    
    unset($i);
    
    if(!empty($outreaches)){
      $count = 1;
      foreach($outreaches as $outreach){
	if($count > 5) {
	  break;
	}
	$OID = $outreach['OID'];
	if(dbIsOutreachOver($OID) ||  dbIsOutreachCancelled($OID)){
	  // DO NOTHING
	} else {
	  $count++;
	  $eventDate = date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID)));
	  $markup .= "<tr>";
	  $markup .= '<td colspan="3">' . '<a href="?q=viewOutreach&OID='.$OID. '">'.chopString($outreach["name"],20) . '</a>' . "</td>";
	  $markup .= '<td colspan="2">' . $eventDate . "</td>";
	  $markup .= '<td><a href="?q=signUp&OID=' . $OID . '"><button type="button">Edit Sign Up</button></a></td>';
	  $markup .= "</tr>";
	}
      }   
    }else{
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="6"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
    $markup .="</table>";

  }
  
  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}

function ideasWaitingApproval() {

  global $user;

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
    $TID = 0;
  }

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
  }

  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  $markup = '<h1>Team Moderator Page</h1><br>';
  $markup .= '<h2>Ideas Awaiting Approval</h2>';

  $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
  $markup .= '<th colspan="2">Log Date</th>';
  $markup .= '<th colspan="2">Owner</th>';
  $markup .= '<th colspan="2"></th>';
  $markup .= "</tr>";

  $outreaches = dbGetIdeasForTeam($TID);

    if(!empty($outreaches)){
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

	if(hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= "<td colspan=\"2\"><a href=\"?q=approveIdea/{$outreach['OID']}/$TID\"><button>Approve</button></a>";
	  $markup .= "<a href=\"?q=rejectIdea/{$outreach['OID']}/$TID\"><button>Reject</button></a></td>";

	} else {
	  $markup .= '<td></td><td></td>';
	}
	$markup .= "</tr>";
      }  
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

function writeUpsWaitingApproval() {

  global $user;

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
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

  $markup = '<h2>Write-Ups Awaiting Approval</h2>';

  $markup .= '<table class="infoTable"><tr><th colspan="3">Name</th>';
  $markup .= '<th colspan="2">Log Date</th>';
  $markup .= '<th colspan="2">Owner</th>';
  $markup .= '<th></th>';
  $markup .= "</tr>";

  $outreaches = dbGetWriteUpsForTeam($TID);

  if(!empty($outreaches)){
    $count = 0;
    foreach($outreaches as $outreach){
      if($outreach['isWriteUpSubmitted'] == true){
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

	if(hasPermissionForTeam('approveIdeas', $TID)){
	  $markup .= '<td><a href="?q=writeupform&approving&OID=' . $OID .'"><button>View</button></a></td>';

	} else {
	  $markup .= '<td></td>';
	}
	$markup .= "</tr>";
      }
    }
    if($count==0){
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
      $markup .= "</tr>";
    }
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

function hoursWaitingApproval() {

  global $user;

  if(dbGetTeamsForUser($user->uid) == NULL){
    drupal_set_message("You don't have a team assigned!", 'error');
    $TID = 0;
  }

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
  }

  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  $markup = '';
  $markup .= '<h2>Hours Awaiting Approval</h2>';
  $markup .= '<table class="infoTable"><tr>';
  $markup .= '<th colspan="2">Outreach</th>';
  $markup .= '<th colspan="2">User</th>';
  $markup .= '<th colspan="2">Hours</th>';
  $markup .= '<th colspan="2"></th>';
  $markup .= "</tr>";

  $filterParams = array('TID' => $TID);
  $hours = dbGetHours($filterParams);

  $count = 0;
    if(!empty($hours)){
      foreach($hours as $hour){
	if($hour['isApproved'] != true){
	  $count++;
	  $OID = $hour['OID'];
	  $outreach = dbGetOutreach($OID);
	  $UID = $hour["UID"];
	  $numHours = $hour['numberOfHours'];
	  $HID = $hour['HID'];

	  $markup .= "<tr>";
	  $markup .= '<td colspan="2">' . '<a href="?q=viewOutreach&OID=' . $outreach['OID'] . '">'.chopString($outreach["name"],20) . '</a>' . '</td>';
	  $markup .= '<td colspan="2">' . dbGetUserName($UID) . ' </td>';
	  $markup .= '<td colspan="2"><a href="?q=logHours&HID='.$HID.'">'.$numHours.'</a></td>';

	  if(canApproveHours($HID)){
	    $markup .= "<td colspan=\"2\"><a href=\"?q=approveHours/{$hour['HID']}\"><button>Approve</button></a>";
	    $markup .= "<a href=\"?q=deleteHours/$HID\">";
	    $markup .= '<button><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></td>';
	  } else {
	    $markup .= '<td></td><td></td>';
	  }
	  $markup .= "</tr>";
	} else {

	}
      }  
      
      if($count == 0){
	$markup .= "<tr>";
	$markup .= '<td style="text-align:center" colspan="8"><em>[None]</em></td>';
	$markup .= "</tr>";
      }

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