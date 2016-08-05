
<?php

/*
  used for display of team information

  - Contents -
  viewTeam() - Displays the information for the team with which the user is currently associated
  teamDashboardHeader() - allows user to switch teams on team dashboard/team preview (not used)
  recentTeamOutreach() - displays the team's outreach based on how recently the outreach was created or by event date
  allTeamOutreach() - displays all outreach for a team
  teamOutreachHeader() - switch teams and add outreach on team dashboard
  teamAllOutreachHeader() - switch teams and add outreach on all team outreach
  viewTeamStatistics() - Displays the total number of hours logged and outreach created for a team
  viewTeamSummary() - Displays preview of team for the team dashboard
*/

//  viewTeam() - Displays the information for the team with which the user is currently associated

function viewTeam() {
  global $user;
  $UID = $user->uid;

  $params = drupal_get_query_parameters();
  $array = array();
  
  // checks to see if the user has a team
  if(isset($params['TID'])) {
    $TID = $params['TID'];
  } else {
    drupal_set_message("No team selected!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);

  }

  // checks to see if the user is on the team (keeping in mind that team owners can
  // see their team application
  if(dbGetTeamOwner($TID) != $UID && (!isMyTeam($TID) || teamIsIneligible($TID))) {
    drupal_set_message('You do not have permission to access this page!', 'error');
    return;
  }
    
  $team = dbGetTeam($TID);

  $markup = '';
  $markup .= '<div style="float:left; width:38%">';
  // create team header and table
  $markup .= '<table style="margin:0px 0px 10px 0px;"><tr>';
    
  $markup .= '<td style="padding:0px 14px 10px 14px;"><div align="left"><h2 style="margin:0px 0px 7px 0px;"><b>';

  // if the team has a type
  if($team['type'] != "Other"){
    $markup .= "{$team['type']} {$team['number']} - {$team['name']}";
  } else{
    $markup .= "Team {$team['number']} - {$team['name']}";
  }
    
  $markup .= '</b></h2></div></td></tr></table>';
    
  // create table
  $markup .= '<table id="photoAndEdit"><tr><td style="padding:0px;">';  

  // if the user can edit team picture
  if(hasPermissionForTeam('editTeam', $TID)){
    $markup .= '<div align="right">';
    $markup .= '<a href= "?q=editThumbnail';
    $markup .= '&TID='. $TID . '&FID=' . $team['FID'] . '">';
    $markup .= '<span title="Edit Photo"><button><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></span></a>';
    $markup .='</div>';
  } else {
    // otherwise show just a disabled button
    $markup .= '<div align="right">';
    $markup .= '<span title="Edit Photo"><button type="button" disabled><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></span>';
    $markup .='</div>';
  }

  $markup .= '</td></tr><tr><td style="padding:0px;">';

  // if the team has a picture then display
  if(!empty($team['FID'])) {
    $url = generateURL($team['FID']);
    $markup .= '<div align="center"><img src="' .$url .'" style="max-width:150px; width:auto; height:auto; padding: 5px 0px 5px 0px">';
    // default team picture
  } else {
    $markup .= '<div align="center"><img src= "/images/defaultPics/team.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">';
  }

  $markup .= '</div></td></tr></table></div>';

  $teams = dbGetTeamsForUser($UID);

  $markup .= '<div align="right">';

  // if the user can permission to manage outreach
  if(!teamIsIneligible($TID) &&
     hasPermissionForTeam('manageOutreachTags', $TID)) {
    $markup .= '<a href="?q=teamModeratorPage">';
    $markup .= '<div class="help tooltip4">';
    $markup .= '<button>Moderators</button>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
    $markup .= 'Click here to view ideas, write-ups, and hours awaiting approval.';
    $markup .= '</span></div></a>';
  } else {
    $markup .= '<div class="help tooltip4">';
    $markup .= '<button type="button" disabled>Moderators</button>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
    $markup .= 'Click here to view ideas, write-ups, and hours awaiting approval.';
    $markup .= '</span></div>';

  }

  // if the user can manage the outreach settings (currently only tags)
  if(!teamIsIneligible($TID) && hasPermissionForTeam('manageOutreachTags', $TID)){
    $markup .= '<a href="?q=teamOutreachSettings">';
    $markup .= '<button>Settings</button></a>';
  } else {
    $markup .= '<button type="button" disabled>Settings</button>';
  }

  // if the user has permission to manage hours
  if(!teamIsIneligible($TID) && hasPermissionForTeam('editAnyHours', $TID)){
    $markup .= '<a href= "?q=offsetHours';
    $markup .= '&TID=' . $team['TID'] . '">';
    $markup .= '<div class="help tooltip4">';
    $markup .= '<button type="button">Enter Old Hours</button>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
    $markup .= 'Click here to enter old team hours from previous years.';
    $markup .= '</span></div></a>';
  } else {
    $markup .= '<div class="help tooltip4">';
    $markup .= '<button type="button" disabled>Enter Old Hours</button>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
    $markup .= 'Click here to enter old team hours from previous years.';
    $markup .= '</span></div>';
  }
  
  // if the user can edit the team
  if(hasPermissionForTeam('editTeam',$TID)){
    $markup .= '<a href= "?q=teamForm&url=viewTeam';
    $markup .= '&TID=' . $team['TID'] . '">';
    $markup .= '<button type="button"><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
  } else{
    $markup .= '<button type="button" disabled><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
  }

  // if the user can delete the team
  if(hasPermissionForTeam('deleteTeam', $TID)){ 
    $markup .= '<a href= "?q=deleteTeamPage';
    $markup .= '&TID=' . $team['TID'] . '">';
    $markup .= '<button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a>';
  } else {
    $markup .= '<button type="button" disabled><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a>';
  }
  
  $markup .= '</div>';

  // begin displaying info

  $markup .= '<div style="width:60%; float:right; padding-left:10px">';

  $teams = dbGetTeamsForUser($UID);
  $numOutreaches = dbGetNumOutreachForTeam($TID);
  
  // create table
  $markup .= '<table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><b>';

  if ($numOutreaches != 0){
    $markup .= '<a href="?q=allTeamOutreach">Outreaches: </a></b>';
  } else {
    $markup .= 'Outreaches: </b>';
  }

  $markup .= $numOutreaches . '</td>';

  $markup .= '<td><b>Total Number of Hours: </b>' . dbGetHoursForTeam($TID) . '</td></tr>';

  $markup .= '<tr><td><b><a href="?q=showUsersForTeam';
  $numStudents = dbGetNumStudentsForTeam($team['TID']);
  $numMentors = dbGetNumMentorsForTeam($team['TID']);
  $markup .= '&TID='.$team['TID'].'&type=student">Students: </a></b>'.dbGetNumStudentsForTeam($team['TID']).'</td>';
  $markup .= '<td><b><a href="?q=showUsersForTeam';
  $markup .= '&TID='.$team['TID'].'&type=mentor">Mentors: </a></b>'.dbGetNumMentorsForTeam($team['TID']).'</td></tr>';

  $markup .= '<tr><td><b>City: </b>' . $team['city'] . '</td>';
  $markup .= '<td><b>State: </b>' . $team['state'] . '</td></tr>';

  $markup .= '<tr><td><b>Country: </b>' . $team['country'] . '</td>';
  $markup .= '<td><b>Rookie Year: </b>' . $team['rookieYear'] . '</td></tr>';

  if($team['rookieYear'] == NULL){
    $team['rookieYear'] = '[none]';
  }

  $markup .= '</table></div>';

  return array('#markup' => $markup);
}

// teamDashboardHeader() - allows user to switch teams on team dashboard/team preview (not used)
function teamDashboardHeader($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $team = getCurrentTeam();
  $form = array();

  // checks to see if a user is on a team
  if(empty($team)) {
    drupal_set_message('You are not currently registered with any teams!');
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    $TID = $team['TID'];
  }

  $teams = dbGetTeamsForUser($UID);
  $choices = array();

  // if user has a team
  if(!empty($teams)) {
    foreach($teams as $userTeam) {
      $choices[$userTeam['TID']] = $userTeam['number'];
    }
  }

  // begin form
  $form['fields']['team'] = array(
    '#type' => 'select',
    '#title' => 'Choose Team',
    '#prefix' => '<table><tr><td style="padding:0px">',
    '#default_value' => $TID,
    '#options' => $choices,
    '#chosen'=>true,
    '#suffix' => '</td>'
				   );

  $form['fields']['submit'] = array(
    '#prefix' => '<td style="padding:0px">',
    '#type' => 'submit',
    '#value' => 'Update',
    '#suffix' => '</td></tr></table>'
				    );

  return $form;
}

function teamDashboardHeader_submit($form, &$form_state)
{
  $fields = array('team');
  $newTID = getFields($fields, $form_state['values'])['team'];
  setCurrentTeam($newTID);
  drupal_set_message('Team changed!');
  drupal_goto('teamDashboard');
  return;
}


// recentTeamOutreach() - displays the team's outreach based on how recently the outreach was created or by event date

function recentTeamOutreach()
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  }

  // checks to see if team can access page
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }
  
  // create header
  $markup = '<h1>Team Dashboard</h1><br>';
  $markup .= '<h2>' . $team['number'] . ' Outreach</h2>';
  $outreaches = dbGetOutreachesForTeam($TID);
  $markup .= '<div align="left" style="float: left">Sort By: ';

  if(isset($params['sortByDate'])) {
    $markup .= '<a href="?q=teamDashboard">Recently Added</a><b> | Upcoming Event</b></div>';
    
    $temp = array();

    foreach($outreaches as $outreach) {
      if(dbGetEarliestTimeForFutureOutreach($outreach['OID']) !== null) {
	$temp[] = $outreach;
      }
    }

    unset($outreach);
    $outreaches = $temp;
    unset($temp);

    // sort outreaches by start date
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

    // sort outreaches by log date
  } else {
    $markup .= '<b>Recently Added | </b><a href="?q=teamDashboard&sortByDate">Upcoming Event</a></div>';
    orderByValue($outreaches, 'logDate', false);
  }
  
  $markup .= '<div align="right" style="float:right">';

  // moderator page button
  if(!teamIsIneligible($TID) &&
     hasPermissionForTeam('manageOutreachTags', $TID)) {
    $markup .= '<a href="?q=teamModeratorPage">';
    $markup .= '<div class="help tooltip4">';
    $markup .= '<button>Moderators</button>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
    $markup .= 'Click here to view ideas, write-ups, and hours awaiting approval.';
    $markup .= '</span></div></a>';
  } else {
    $markup .= '';
  }

  // all team outreach button
  $markup .= '<a href="?q=outreach&allTeamOutreach"><div class="help tooltip3"><button>All Team Outreach</button><span id="helptext"; class="helptext tooltiptext3">Click here to view all of your teams outreach.</span></div></a></div>';

  // create table
  $markup .= '<table class="infoTable"><tr><th>Name</th><th>Event Date</th><th>Owner</th><th></th></tr>';
  
  $count = 1;

  // displays eight outreaches
    foreach($outreaches as $outreach) {
      if($count > 7) {
	break;
    }

    if($outreach['cancelled']) {
      continue;
    }

    $count++;

    $OID = $outreach['OID'];
    $markup .= '<tr><td><a href="?q=viewOutreach&OID=' . $OID . '"</a>'; 
    $markup .= $outreach["name"] . '</td>';

    // displays event date
    if(null !== dbGetEarliestTimeForOutreach($OID)) {
      $markup .= '<td>' . date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID))) . '</td>';
    } else {
      $markup .= '<td>[none]</td>';
    }

    $owner = dbGetOutreachOwner($OID);

    // DISPLAYS OUTREACH OWNER
    if ($owner != false){
      $markup .= '<td><a href="?q=viewUser&UID=' . $owner . '">' . dbGetUserName($owner) . '</a></td>';
    } else {
      $markup .= '<td>[none]</td>'; // insert placeholder if no outreach owner
    }

    $markup .= '<td>';

    // SIGN UP FOR OUTREACH BUTTON
    $signUp = dbIsUserSignedUp($UID, $OID);

    if(dbIsUserSignedUp($UID, $OID)){
      if(dbIsOutreachOver($OID)){
	$markup .= '<a href="?q=signUp&OID=' . $OID . '"><button type="button" disabled>Edit Sign Up</button></a>';
      }
      else{
	$markup .= '<a href="?q=signUp&OID=' . $OID . '"><button type="button">Edit Sign Up</button></div></a>';
      }
    } else{
      if(dbIsOutreachOver($OID)){
	$markup .= '<a href="?q=signUp&OID=' . $OID . '"><button type="button" disabled>Sign Up</button></div></a>';
      }
      else{
	$markup .= '<a href="?q=signUp&OID=' . $OID . '"><button type="button">Sign Up</button></div></a>';
      }
    }

    $markup .= '</td></tr>';
  }

    // IF NO OUTREACHES FOR TEAM
  if(empty($outreaches) || $count == 1) {
    $markup .= '<tr><td colspan="10">No outreach found! Click <a href="?q=outreachForm">here</a> to create new outreach!</td></tr></table>';
    return array('#markup' => $markup);
  }

  unset($count);
  
  $markup .= '</table>';

  return array('#markup' => $markup);
}

// allTeamOutreach() - displays all outreach for a team
function allTeamOutreach()
{
  global $user;
  $params = drupal_get_query_parameters();
  $currentYear = date("Y");
  $totalFilterHours = 0;
  $search = false;

  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
    $teamNumber = $team['number'];
  }

  // CHECKS TO SEE IF TEAM HAS PERMISSION TO ACCESS PAGE
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }
  
  if(isset($params["cancelled"])){
    $outreaches = dbGetCancelledOutreach($TID);
  } else if(isset($params['query']) && $params['query'] == 'search'){
    $outreaches = dbSearchOutreach($_SESSION['searchSQL'], $_SESSION['proxyFields']);
    $search = true;
  } else if(isset($params['tag'])) {
    $proxyFields = array();
    $outreaches = dbSearchOutreach(generateSearchSQL(array('tags' => array($params['tag']), 'TID' => array('value' => $TID, 'matchType' => 'exact')), $proxyFields), $proxyFields); // use dbSearchOutreach to select outreach on team with given tag
  } else if(isset($params['status']) && $params['status'] != 'all') {
    $proxyFields = array();
    $outreaches = dbSearchOutreach(generateSearchSQL(array('status' => array($params['status']), 'TID' => array('value' => $TID, 'matchType' => 'exact')), $proxyFields), $proxyFields); // use dbSearchOutreach to select outreach on team with given status
  } else {
    $outreaches = dbGetOutreachesForTeam($TID);
  }

  // CREATE HEADER
  $markup = '<h1>All Team Outreach</h1>';

  // IF SEARCH RESULTS, THEN HEADER CHANGES
  if($search) {
    $markup .= '<h2>Search Results (';
    $markup .= empty($outreaches) ? '0' : count($outreaches);
    $markup .= ' matches)</h2>';
  } else if(isset($params['tag'])) {
    $markup .= '<h2>Outreaches Tagged "' . dbGetTagName($params['tag']) . '"</h2>';
  } else {
    $markup .= "<h2>Outreach for Team $teamNumber</h2>";
  }

  if(empty($outreaches)) {
    $outreaches = array();
  }

  foreach ($outreaches as &$outreach){
    $OID = $outreach['OID'];
    $outreach['hours'] = dbGetHoursForOutreach($OID);
    $totalFilterHours += $outreach['hours'];
  }    

  unset($outreach);
  $sortParam = isset($params["sort"]) ? $params['sort'] : 'name';
  $statusParam = isset($params["status"]) ? $params['status'] : 'all';
  orderByValue($outreaches, $sortParam, true); // custom function (see helperFunctions.inc)

  // OPTIONS TO SORT BY
  $markup .= '<table style="margin:0px"><tr><td style="padding:0px" align="left">Sort By: ';

  if($search) {
    $markup .= '<a href="?q=allTeamOutreach&sort=name"> Name</a><b> | </b><a href="?q=allTeamOutreach&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&sort=eventDate">Event Date</a></td>';
  } else {
    switch($sortParam) {
    case 'name':
      $markup .= '<b>Name</b><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'status':
      $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><b>Status | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'hours':
      $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><b>Hours | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    case 'eventDate':
      $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><b>Event Date</b>';
      break;
    default:
      $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=eventDate">Event Date</a>';
      break;
    }
  }

  // DISPLAYS HOURS WITH CURRENT FILTERS
  $markup .= '</td><td style="padding:0px; text-align:right" align="right"><b>Hours with Current Filters: </b>' . $totalFilterHours . '</td></tr>';
  $markup .= '<tr><td style="padding:0px">Include Status: ';

  // OPTIONS TO SORT BY
  if($search) {
    $markup .= '<a href="?q=allTeamOutreach">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp">Write Up</a></td>';
  } else {
    switch($statusParam) {
    case 'all':
      $markup .= '<b>All</b><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'isIdea':
      $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><b>Idea | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'isOutreach':
      $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><b>Outreach | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    case 'doingWriteUp':
      $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><b>Write Up</b>';
      break;
    default:
      $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a>';
      break;
    }
  }

  $markup .= '</td><td style="padding:0px; text-align:right">';

  // ALL OUTREACHES BUTTON
  if($search) {
    $markup .= '<a href="?q=allTeamOutreach"><button>All Outreaches</button></a>';
  }

  // IF TEAM MODERATOR, ADMIN, OR OWNER
   if(hasPermissionForTeam('manageOutreachTags', $TID)){
    $markup .= '<a href="?q=teamOutreachSettings"><div class="help tooltip1"><button>Settings</button><span id="helptext"; class="helptext tooltiptext1">If you have permission, click here to manage your teams outreach tags and outreach visibilities.</span></div></a>';
  } else {
    }
   
   // DISPLAY CANCELLED OUTREACH FOR TEAM
   if(!isset($params["cancelled"])){
     $markup .= '<a href="?q=allTeamOutreach&cancelled"><div class="help tooltip1"><button>Cancelled</button><span id="helptext"; class="helptext tooltiptext1">Click here to view all of your teams cancelled outreach.</span></div></a>';
   } else {
     $markup .= '<a href="?q=allTeamOutreach"><button>All Outreach</button></a>';
   }

  $markup .= '</td></tr></table>';
  // CREATE TABLE
  $markup .= '<table class="infoTable" style="margin:0px"><tr><th>Status</th>';
  $markup .= '<th>Name</th>';
  $markup .= '<th>Owner</th>';
  $markup .= '<th>Event Date</th>';

  // IF TEAM HAS NO OUTREACHES
  if(empty($outreaches)) {
    $markup .= '<tr><td colspan="4">No outreach found! Click <a href="?q=outreachForm">here</a> to create new outreach!</td></tr></table>';
    return array('#markup' => $markup);
  }

  foreach($outreaches as $outreach) {
    $OID = $outreach['OID'];
    $hours = dbGetHoursForOutreach($OID);
    $status;
    
    // DISPLAY OUTREACH BY STATUS
    switch($outreach['status']) {
    case 'isOutreach': $status = '<span title="Outreach Event"><img class="eventIndicatorIcon" src="/images/icons/outreachBlue.png"></span>'; break;
    case 'isIdea': $status = '<span title="Idea"><img class="eventIndicatorIcon" src="/images/icons/ideaBlue.png"></span>'; break;
    case 'doingWriteUp': $status = '<span title="Write Up"><img class="eventIndicatorIcon" src="/images/icons/writeUpBlue.png"></span>
'; break;
    case 'locked': $status = '<span title="Locked Event"><img class="eventIndicatorIcon" src="/images/icons/lockedBlue.png"></span>'; break;
    }  
    $markup .= "<tr><td>$status</td>";
    
    $markup .= "<td><a href='?q=viewOutreach&OID=$OID'>" . chopString($outreach['name'], 20) . '</a></td>';
    
    // DISPLAY OUTREACH OWNER
    $owner = dbGetOutreachOwner($OID);

    if ($owner != false){
      $markup .= '<td><a href="?q=viewUser&UID=' . $owner . '">' . dbGetUserName($owner) . '</a></td>';
    } else {
      $markup .= '<td>[none]</td>';
    }
   
    // DISPLAY EVENT DATE
    if(null != dbGetEarliestTimeForOutreach($OID)) {
      $markup .= '<td>' . date(TIME_FORMAT, strtotime(dbGetEarliestTimeForOutreach($OID))) . '</td>';
    } else {
      $markup .= '<td>[none]</td>';
    }

  }
  
  $markup .= '</table>';

  return array('#markup' => $markup);
}

// teamOutreachHeader() - switch teams and add outreach on team dashboard
function teamOutreachHeader($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $team = getCurrentTeam();
  $teams = dbGetTeamsForUser($UID);
  $form = array();
  
  // IF TEAM IS NOT EMPTY 
  if(!empty($teams)) {
    $TID = $team['TID'];
    
    $form['fields']['header'] = array(
				      '#markup' => '<table id="outreachPageHeader"><td id="addOutreachForTeamText" style="text-align:center; padding:0px"><h2>Add Outreach For '
				      );
    // IF USER HAS MULTIPLE TEAMS THEN ABLE TO CHANGE TEAM
    if(count($teams) != 1){
      $choices = array();
    
      foreach($teams as $userTeam) {
	$choices[$userTeam['TID']] = $userTeam['number'];
      }

      // BEGIN FORM
      $form['fields']['team'] = array(
				      '#prefix' => '</h2></td><td style="padding:4px">',
				      '#type' => 'select',
				      '#default_value' => $TID,
				      '#options' => $choices,
				      '#chosen'=>true,
				      '#attributes' => array('onChange' => 'document.getElementById("teamoutreachheader").submit();'),
				      '#suffix' => '</td></tr>'
				      );
    } else {
      // IF USER HAS ONE TEAM THEN DISPLAY TEAM NUMBER
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

  // BUTTON TO ADD OUTREACH
  $form['button'] = array(
			  '#markup' => '<tr><td colspan="2" style="text-align:right; padding:0px"><a href="?q=outreachForm"><center><div class="help tooltip4"><button type="button" class="largeButton">+ Outreach</button><span id="helptext"; class="helptext tooltiptext4">Click here to add an outreach for your currently active team.</span></div></button></center></a></td></tr></table>'
			  );
  
  return $form; 
}

function teamOutreachHeader_submit($form, &$form_state)
{
  $fields = array('team');
  $newTID = getFields($fields, $form_state['values'])['team'];
  setCurrentTeam($newTID);
  $teamNumber = dbGetTeamNumber($newTID);
  drupal_set_message("Now operating under Team $teamNumber!");
  drupal_goto('teamDashboard');
  return;
}

//  teamAllOutreachHeader() - switch teams and add outreach on all team outreach
function teamAllOutreachHeader($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $team = getCurrentTeam();
  $teams = dbGetTeamsForUser($UID);
  $form = array();
  
  // IF TEAM IS NOT EMPTY 
  if(!empty($teams)) {
    $TID = $team['TID'];
    
    $form['fields']['header'] = array(
				      '#markup' => '<table id="outreachPageHeader" style="margin:86px 0px 0px 0px"><tr><td id="addOutreachForTeamText" style="text-align:center; padding:0px"><h2>Add Outreach For '
				      );
    // IF USER HAS MULTIPLE TEAMS THEN ABLE TO CHANGE TEAM
    if(count($teams) != 1){
      $choices = array();
    
      foreach($teams as $userTeam) {
	$choices[$userTeam['TID']] = $userTeam['number'];
      }
      
      // BEGIN FORM
      $form['fields']['team'] = array(
				      '#prefix' => '</h2></td><td style="padding:4px">',
				      '#type' => 'select',
				      '#default_value' => $TID,
				      '#options' => $choices,
				      '#chosen'=>true,
				      '#attributes' => array('onChange' => 'document.getElementById("teamalloutreachheader").submit();'),
				      '#suffix' => '</td></tr>'
				      );

      // IF USER HAS ONE TEAM THEN DISPLAY TEAM NUMBER
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
  
  // BUTTON TO ADD OUTREACH
  $form['button'] = array(
			  '#markup' => '<tr><td colspan="2" style="text-align:right; padding:0px"><a href="?q=outreachForm"><center><div class="help tooltip4"><button type="button" class="largeButton">+ Outreach</button><span id="helptext"; class="helptext tooltiptext4">Click here to add an outreach for your currently active team.</span></div></button></center></a></td></tr></table>'
			  );
  
  return $form; 
}

function teamAllOutreachHeader_submit($form, &$form_state)
{
  $fields = array('team');
  $newTID = getFields($fields, $form_state['values'])['team'];
  setCurrentTeam($newTID);
  $teamNumber = dbGetTeamNumber($newTID);
  drupal_set_message("Now operating under Team $teamNumber!");
  drupal_goto('allTeamOutreach');
  return;
}



// viewTeamStatistics() - Displays the total number of hours logged and outreach created for a team

function viewTeamStatistics() {
  global $user;
  $params = drupal_get_query_parameters();
  $UID = $user->uid;

  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  } else {
    $currentTeam = getCurrentTeam();
    $TID = $currentTeam['TID'];
    $teamNumber = $currentTeam['number'];
  }

  // CHECKS IF TEAM HAS PERMISSION TO ACCES PAGE
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }
  
  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= '<script src="numberCounting.js"></script>';
  // CREATE TABLE AND HEADER
  $markup .= '<table id="teamStats"><tr><td colspan="2" style="text-align:center">';
  
  $markup .= '<div class="help tooltip1">';
  $markup .= '<h2><b>Team Stats</b></h2>';
  $markup .= '<span id="helptext"; class="helptext tooltiptext1">';
  $markup .= 'These are the total numbers of hours and outreaches your team has inputted into CROMA.';
  $markup .= '</span></div>';
  $markup .= '</td></tr>';

  // LINKS TO ALL TEAM OUTREACH PAGE
  $markup .= '<tr><td style="text-align:center"><a href="?q=allTeamOutreach"><b>HOURS</b></a></td>';
  $markup .= '<td style="text-align:center"><a href="?q=allTeamOutreach"><b>OUTREACHES</b></a></td></tr>';
  $markup .= '<tr style="font-size:48pt; font-family: "Open Sans", sans-serif;"><td style="text-align:center"><b class="countUp">' . dbGetHoursForTeam($TID) . '</a></b></td>';
  $markup .= '<td style="text-align:center"><b class="countUp">' .   dbGetNumOutreachForTeam($TID);
  $markup .= '</b></td></tr></table>';

  return array('#markup' => $markup);
}

//  viewTeamSummary() - Displays preview of team for the team dashboard

function teamSummary($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $team = getCurrentTeam();
  $teams = dbGetTeamsForUser($UID);
  $form = array();

  if(isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  }

  // IF TEAM IS NOT EMPTY 
  if(!empty($teams)) {
    $TID = $team['TID'];
    
    if(count($teams) != 1){
      // IF USER HAS MULTIPLE TEAMS THEN ABLE TO CHANGE TEAM
      $multiple = true;
      $choices = array();
    } else {
      // IF USER HAS ONE TEAM THEN DISPLAY TEAM NUMBER
      $multiple = false;
    }
    
    foreach($teams as $userTeam) {
      $choices[$userTeam['TID']] = $userTeam['number'];
    }

    $markup = '<table id="teamPageSummary" style="margin:112px 0px 0px 0px; padding:0px"><tr style="text-align:center"><td ';
    if (!$multiple){
      $markup .= 'colspan="2" style="text-align:center;';
    } else {
      $markup .= 'style="text-align:right;';
    }
    $markup .= ' width:50%; padding:0px"><h2>' . $team['type'] . ' ';
    $form['fields']['header'] = array(
				      '#markup' => $markup
				      );

    if($multiple){
      $form['fields']['team'] = array(
				      '#prefix' => '</h2></td><td style="width:50%;padding:0px">',
				      '#type' => 'select',
				      '#default_value' => $TID,
				      '#options' => $choices,
				      '#chosen'=>true,
				      '#attributes' => array('onChange' => 'document.getElementById("teamsummary").submit();'),
				      '#suffix' => '</td></tr>'
				      );
    } else {
      $form['fields']['team'] = array(
				      '#markup' => $team['number'] . '</h2></td></tr>'
				      );
    }

    if(!empty($team['FID'])) {
      $FID = $team['FID'];
      $file = file_load($FID);
      $uri = $file->uri;
      $variables = array('style_name'=>'profile','path'=>$uri,'width'=>'150','height'=>'150');
      $image = theme_image_style($variables);
      $form['fields']['FID'] = array(
				     '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
				     '#type'=>'item',
				     '#markup'=> $image,
				     '#suffix'=>'</td></tr>'
				     );

    } else {
      // IF TEAM DOES NOT HAVE A PICTURE, THEN DISPLAYS DEFAULT PIC
      $form['fields']['FID'] = array(
				     '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
				     '#markup'=>'<img src="/images/defaultPics/team.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">',
				     '#suffix'=>'</td></tr>'
				     );
    }

    $form['fields']['markupOne'] = array(
					 '#markup' => '<tr><td style="text-align:left"><a href="?q=viewTeam&TID='. $TID .'"><div class="help tooltip4"><button type="button">Team Info</button><span id="helptext"; class="helptext tooltiptext4">Click here to view/edit your team info or to enter old team hours.</span></div></a></td>'
					 );

    if(hasPermissionForTeam('manageOutreachTags', $TID)){
    $form['fields']['markupTwo'] = array(
					 '#markup' => '<td colspan="2" style="text-align:right"><a href="?q=teamOutreachSettings"><div class="help tooltip3"><button type="button">Settings</button><span id="helptext"; class="helptext tooltiptext3">If you have permission, click here to manage your teams outreach tags and outreach visibilities.</span></div></a></td></tr></table>'
					 );

    }else{
    $form['fields']['markupTwo'] = array(
					 '#markup' => '<td colspan="2" style="text-align:right"><div class="help tooltip3"><button type="button" disabled>Settings</button><span id="helptext"; class="helptext tooltiptext3">If you have permission, click here to manage your teams outreach tags and outreach visibilities.</span></div></td></tr></table>'
					 );
    }


    if($multiple){
      $form['fields']['submit'] = array(
					'#type' => 'submit',
					'#value' => 'Update',
					// the submit itself is necessary, but now it can be hidden
					'#attributes' => array(
							       'style' => array('display: none;'),
							       ),
					);
    }


  }
  return $form;
}

function teamSummary_submit($form, &$form_state)
{
  $fields = array('team');
  $newTID = getFields($fields, $form_state['values'])['team'];
  setCurrentTeam($newTID);
  $teamNumber = dbGetTeamNumber($newTID);
  drupal_set_message("Now operating under Team $teamNumber!");
  drupal_goto('teamDashboard');
}



/*function teamSummary()
{
  global $user;
  $params = drupal_get_query_parameters();


  if(isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamNumber = $team['number'];
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
    $teamNumber = $team['number'];
  }

  $teamInfo = dbGetTeam($TID);

  // DISPLAYS TEAM TYPE AND NUMBER
  $markup = '<table id="miniViewTeam" style="margin:112px 0px 0px 0px"><tr><td colspan="6" style="text-align:center"><h2><b>' . $teamInfo['type'] .' '. $teamInfo['number'] . '</b></h2></td></tr>';

  $markup .='<tr><td colspan="6" style="text-align:center">';

  // IF TEAM HAS A PICTURE, THEN DISPLAYS
  if(!empty($teamInfo['FID'])) {
    $FID = $teamInfo['FID'];
    $file = file_load($FID);
    $uri = $file->uri;
    $variables = array('style_name'=>'profile','path'=>$uri,'width'=>'150','height'=>'150');
    $image = theme_image_style($variables);
    $markup .= $image;
  } else {
    // IF TEAM DOES NOT HAVE A PICTURE, THEN DISPLAYS DEFAULT PIC
    $markup .= '<img src="/images/defaultPics/team.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">';
    }

  $markup .='</td></tr>';

  $markup .= '<tr><td colspan="3" style="text-align:left"><a href="?q=viewTeam&TID='. $TID .'">';

  // BUTTON TO VIEW FULL TEAM INFO
  $markup .= '<div class="help tooltip4"><button type="button">Team Info</button>';
  $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
  $markup .= 'Click here to view/edit your team info or to enter old team hours.';
  $markup .= '</span></div></a></td>';

  $markup .= '<td colspan="3" style="text-align:right">';

  // BUTTON TO VIEW TEAM OUTREACH SETTINGS IF MODERATOR, ADMIN, OR OWNER
  if(hasPermissionForTeam('manageOutreachTags', $TID)){
    $markup .= '<a href="?q=teamOutreachSettings">';
    $markup .= '<div class="help tooltip3"><button>Settings</button><span id="helptext"; class="helptext tooltiptext3">If you have permission, click here to manage your teams outreach tags and outreach visibilities.</span></div></a>';
  } else {
    // DISABLED BUTTON FOR STUDENTS
    $markup .= '<a href="?q=teamOutreachSettings">';
    $markup .= '<div class="help tooltip3"><button type="button" disabled>Settings</button><span id="helptext"; class="helptext tooltiptext3">If you have permission, click here to manage your teams outreach tags and outreach visibilities.</span></div></a>';
  }

  $markup .= '</td>';

  
  $markup .='</tr></table>';

  return array('#markup' => $markup);
  }*/

?>