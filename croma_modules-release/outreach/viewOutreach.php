<?php

/*
  ---- outreach/viewOutreach.php ----

  used for master display of outreach events from the search form

  - Contents -
  viewOutreach() - takes in filters and sorting to display all possible outreach data
*/

function viewOutreach()
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= '<script src="numberCounting.js"></script>';
  $markup .= '<h1>Outreach</h1><br>';

  // if doing a custom search
  if(isset($params['query']) && $params['query'] == 'search'){   
    $sql = generateSearchSQL($_SESSION['searchParams'], $_SESSION['proxyFields']);
    $outreaches = dbSearchOutreach($sql, $_SESSION['proxyFields']);

    $header = '<h2>Custom Search Results (';
    $header .= empty($outreaches) ? '0' : count($outreaches);
    $header .= ' matches)</h2>';
  }
  // if searching for outreaches with a given tag
  else if(isset($params['tag'])) { 

    $_SESSION['searchParams'] = array('tags'=>array($params['tag']));
    $_SESSION['proxyFields'] = array();
    $sql = generateSearchSQL($_SESSION['searchParams'], $_SESSION['proxyFields']);
    $outreaches = dbSearchOutreach($sql, $_SESSION['proxyFields']);

    $header = '<h2>Outreaches Tagged "' . dbGetTagName($params['tag']) . '"</h2>';
  }
  // if searching for outreaches owned by the given user
  else if(isset($params['owned'])) {
    $outreaches = dbGetOwnedOutreachForUser($UID);
    $header = '<h2>Outreaches I Own</h2>';
  }
  // if searching for outreaches the user is signed up for
  else if(isset($params['signedUp'])) {
    $outreaches = dbGetOutreachForUser($UID);
    $header = '<h2>Outreaches I Am Signed Up For</h2>';
  }
  // if searching all outreach for a team
  else if (isset($params['allTeamOutreach'])){
    $TID = getCurrentTeam()['TID'];
    $_SESSION['searchParams'] = array('TID'=> array('value'=>$TID, 'matchType'=>'exact'));
    $_SESSION['proxyFields'] = array(':TID'=>$TID);
    $sql = generateSearchSQL($_SESSION['searchParams'], $_SESSION['proxyFields']);
    $outreaches = dbSearchOutreach($sql, $_SESSION['proxyFields']);

    $teamName = dbGetTeamName($TID);
    $header = "<h2>All Outreach for $teamName</h2>";
  } 
  // if no search selected
  else {
    $_SESSION['searchParams'] = array();
    $_SESSION['proxyFields'] = array();
    $header = "<h2>No Search Selected</h2>";
  }

  if (isset($_SESSION['searchParams']['teams']) && count($_SESSION['searchParams']['teams']) > 1){
    $multipleTeamsInResult = true;
  } else {
    $multipleTeamsInResult = false;
  }

  $markup .= $header;

  // set $outreaches to an array rather than false (so that later functions don't have errors)
  if(empty($outreaches)) {
    $outreaches = array();
  }

  $totalFilterOutreaches = count($outreaches);
  $totalFilterHours = 0;

  foreach ($outreaches as &$outreach){
      $outreach['hours'] = dbGetHoursForOutreach($outreach['OID']);
      $totalFilterHours += $outreach['hours'];
  }
  unset($outreach);

  $sortParam = isset($params["sort"]) ? $params['sort'] : 'name';
  $isAscending = isset($params['isAscending']) ? true : false;
  orderByValue($outreaches, $sortParam, $isAscending); // custom function (see helperFunctions.inc)
  
  $markup .= '<table style="margin:0px">';
  $markup .= '<tr><td style="padding:0px; text-align:left"><b>Outreaches with Current Filters: </b><span class="countUp">' . $totalFilterOutreaches .'</span></td>';
  $markup .= '<td style="padding:0px; text-align:right" align="right"><b>Hours with Current Filters: </b><span class="countUp">' . $totalFilterHours . '</span></td></tr>';

  $markup .= '<tr><td style="padding:0px" align="left">Sort By: ';

  // remove special params (since they should not be added every time)
  unset($params['isAscending']);
  unset($params['sort']);

  $markup .= sortHeader($sortParam, $params, $isAscending, 'Name', 'name', 'outreach') . ' | ';
  $markup .= sortHeader($sortParam, $params, $isAscending, 'Status', 'status', 'outreach') . ' | ';
  $markup .= sortHeader($sortParam, $params, $isAscending, 'Hours', 'hours', 'outreach') . ' | ';
  $markup .= sortHeader($sortParam, $params, $isAscending, 'Event Date', 'eventDate', 'outreach');

  $markup .= '</td><td style="padding:0px; text-align:right">';

  if(!isset($params['owned'])){
    $markup .= '<a href="?q=outreach&owned"><div class="help tooltip4"><button>Owned</button><span id="helptext"; class="helptext tooltiptext4">Click here to sort by outreach you own.</span></div></a>';
  } else {
    $markup .= '<a href="?q=outreach&allTeamOutreach"><button>All Team Outreach</button></a>';
  }

  if(!isset($params['signedUp'])){
    $markup .= '<a href="?q=outreach&signedUp"><div class="help tooltip3"><button>Signed Up</button><span id="helptext"; class="helptext tooltiptext3">Click here to sort by outreach you are signed up for.</span></div></a>';
  } else {
    $markup .= '<a href="?q=outreach&allTeamOutreach"><button>All Team Outreach</button></a>';
  }

  $markup .= '</td></tr></table>';
  $markup .= '<table class="infoTable" style="margin:0px"><tr><th colspan="2">Status</th>';
  if ($multipleTeamsInResult){
    $markup .= '<th colspan="2">Team</th>';
  }
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
    default:
      drupal_set_message('Invalid outreach data.');
      break;
    }  

    $markup .= '<tr><td colspan="2" style="padding: 0px 0px 0px 14px;">';
    $markup .= showOutreachStatusIcon($outreach['status']) . '</td>';
    if($multipleTeamsInResult) {
      $markup .= '<td colspan="2"><a href="?q=viewTeam&TID=' . $outreach['TID'] . '">' . dbGetTeamNumber($outreach['TID']) . '</a></td>';
    }
    $markup .= '<td colspan="4"><a href="?q=viewOutreach&OID=' .$OID . '">' . chopString($outreach['name'], 15) . '</a></td>';
    $markup .= '<td colspan="2">' . $hours . '</td>';


    if(dbGetEarliestTimeForOutreach($OID) != false) {
      $markup .= '<td colspan="2">' . date(TIME_FORMAT, dbDateSQL2PHP(dbGetEarliestTimeForOutreach($OID))) . '</td>';
    } else {
      $markup .= '<td colspan="2">[none]</td>';
    }
  }
  
  $markup .= '</table>';
  return array('#markup' => $markup);
}

?>