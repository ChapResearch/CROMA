<?php
function viewUserOutreach()
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
  } else if(isset($params['tag'])) {
    $proxyFields = array();
    $outreaches = dbSearchOutreach(generateSearchSQL(array('tags' => array($params['tag'])), $proxyFields), $proxyFields); // use dbSearchOutreach to select outreach on team with given tag
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
    $TID = getCurrentTeam()['TID'];
    $_SESSION['searchParams'] = array('TID'=> array('value'=>$TID, 'matchType'=>'exact'));
    $_SESSION['proxyFields'] = array(':TID'=>$TID);
    $_SESSION['searchSQL'] = generateSearchSQL($_SESSION['searchParams'], $_SESSION['proxyFields']);
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
  } else if(isset($params['tag'])) {
    $markup .= '<h2>Outreach Tagged "' . dbGetTagName($params['tag']) . '"</h2>';
  } else if (isset($params['allTeamOutreach'])){
    $teamName = dbGetTeamName($TID);
    $markup .= "<h2>All Outreach for $teamName</h2>";
  } else {
    $markup .= "<h2>All Outreach for $userName</h2>";
  }

  if(empty($outreaches)) {
    $outreaches = array();
  }

  $totalFilterOutreaches = count($outreaches);
  
  $count = 0;
  foreach ($outreaches as &$outreach){
    //    if (!$outreach['cancelled']){
      $outreach['hours'] = dbGetHoursForOutreach($outreach['OID']);
      $totalFilterHours += $outreach['hours'];
      if ($outreach['hours'] != 0){
	$count++;
      }
      //    }
  }

  dpm($count, 'count');

  unset($outreach);
  $sortParam = isset($params["sort"]) ? $params['sort'] : 'name';
  $statusParam = isset($params['status']) ? $params['status'] : 'all';
  $isAscending = isset($params['isAscending']) ? false : true;
  orderByValue($outreaches, $sortParam, $isAscending); // custom function (see helperFunctions.inc)
  $markup .= '<table style="margin:0px">';
  $markup .= '<tr><td style="padding:0px; text-align:left"><b>Outreaches with Current Filters: </b><span class="countUp">' . $totalFilterOutreaches .'</span></td>';
  $markup .= '<td style="padding:0px; text-align:right" align="right"><b>Hours with Current Filters: </b><span class="countUp">' . $totalFilterHours . '</span></td></tr>';

  $markup .= '<tr><td style="padding:0px" align="left">Sort By: ';
  //  if($search) {
    //    $markup .= ' (Clears Search): ';
  if (isset($search)){
    $extraParams['query'] = 'search';
  }
  if (isset($params['allTeamOutreach'])){
    $extraParams['allTeamOutreach'] = true;
  }
  $markup .= sortHeader($sortParam, $extraParams, $isAscending, 'Name', 'name', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, $extraParams, $isAscending, 'Status', 'status', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, $extraParams, $isAscending, 'Hours', 'hours', 'outreach') . ' | ';
    $markup .= sortHeader($sortParam, $extraParams, $isAscending, 'Event Date', 'eventDate', 'outreach');
    /*  } else {
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
    */


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
  $markup .= '</td><td style="padding:0px; text-align:right">';

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
    default:
      drupal_set_message('Bad outreach data!');
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
}

?>