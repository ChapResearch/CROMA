<?php

function viewUserOutreach()
{
  global $user;
  $UID = $user->uid;
  $userName = dbGetUserName($UID);
  $params = drupal_get_query_parameters();
  $currentYear = date("Y");
  $totalFilterHours = 0;

  $markup = "<h1>All Outreach for $userName</h1>";
  if(isset($params["cancelled"])){
    $outreaches = dbGetCancelledOutreach($TID);
  } else if(isset($params['query']) && $params['query'] == 'search'){
    $outreaches = dbSearchOutreach($_SESSION['searchSQL'], $_SESSION['proxyFields']);
  } else {
    $outreaches = dbGetOutreachesForTeam($TID);
 
    if(isset($params['status']) && $params['status'] != 'all') {
      $proxyFields = array();
      $outreaches = dbSearchOutreach(generateSearchSQL(array('status' => array($params['status'])), $proxyFields), $proxyFields);
    }
  }

  foreach ($outreaches as &$outreach){
    $outreach['hours'] = dbGetHoursForOutreach($outreach['OID']);
    $totalFilterHours += $outreach['hours'];
  }    

  unset($outreach);
  $markup .= '<div style="float:left"><h4>Hours With Current Filters: ' . $totalFilterHours. '</h4></div><br><br>';
  $sortParam = isset($params["sort"]) ? $params['sort'] : 'name';
  $statusParam = isset($params["status"]) ? $params['status'] : 'all';
  orderByValue($outreaches, $sortParam, true); // custom function (see helperFunctions.inc)
  $markup .= '<div align="left" style="float: left">Sort By: ';

  switch($sortParam) {
  case 'name':
    $markup .= '<b>Name</b><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=logDate">Log Date</a><br>';
    break;
  case 'status':
    $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><b>Status | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=logDate">Log Date</a><br>';
    break;
  case 'hours':
    $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><b>Hours | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=logDate">Log Date</a><br>';
    break;
  case 'logDate':
    $markup .= '<a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=name">Name</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=status">Status</a><b> | </b><a href="?q=allTeamOutreach&status=' . $statusParam . '&sort=hours">Hours</a><b> | </b><b>Log Date</b><br>';
    break;
  }

  $markup .= 'Include Status: ';

  switch($statusParam) {
  case 'isIdea':
    $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><b>Idea | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a><br>';
    break;
  case 'isOutreach':
    $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><b>Outreach | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a><br>';
    break;
  case 'doingWriteUp':
    $markup .= '<a href="?q=allTeamOutreach&sort=' . $sortParam . '">All</a><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><b>Write Up</b><br>';
    break;
  default:
    $markup .= '<b>All</b><b> | </b><a href="?q=allTeamOutreach&status=isIdea&sort=' . $sortParam . '">Idea</a><b> | </b><a href="?q=allTeamOutreach&status=isOutreach&sort=' . $sortParam . '">Outreach</a><b> | </b><a href="?q=allTeamOutreach&status=doingWriteUp&sort=' . $sortParam . '">Write Up</a><br>';
  }
  
  $markup .= '</div>';
  orderByValue($outreaches, $sortParam, true); // custom function (see helperFunctions.inc) 

  // Begin Displaying Outreach Events
  $markup .= '<div align="right" style="float:right"><a href="?q=searchForm"><button>Search</button></a>';

  if(!isset($params["cancelled"])){
    $markup .= '<a href="?q=allTeamOutreach&cancelled"><button>Cancelled</button></a>';
  } else {
    $markup .= '<a href="?q=allTeamOutreach"><button>All Outreaches</button></a>';
  }

  $markup .= '<a href="?q=viewOutreachSettings"><button';
  $markup .= dbGetRIDForTeam($user->uid, $TID) > 0 ? '' : ' disabled';
  $markup .= '>Outreach Settings</button></a>';
  $markup .= '</div><br>';
  $markup .= '<table><tr><th>Name</th>';
  $markup .= '<th>Description</th>';
  $markup .= '<th>Status</th>';
  $markup .= '<th>Hours</th>';
  $markup .= '<th>Log Date</th>';

  foreach($outreaches as $outreach) {
    $hours = dbGetHoursForOutreach($outreach['OID']);
    $status;
    
    switch($outreach['status']) {
    case 'isOutreach':
      $status = 'Outreach';
      break;
    case 'isIdea':
      $status = 'Idea';
      break;
    case 'doingWriteUp':
      $status = 'Write-Up';
      break;
    default:
      $status = '[none]';
      break;
    }
    
    $markup .= "<tr><td><a href='?q=viewOutreach&OID={$outreach['OID']}'>{$outreach['name']}</a></td>";
    
    if(isset($outreach['description'])) {
      $markup .= "<td>" . chopString($outreach['description'], 50) . "</td>";
    } else {
      $markup .= '<td>[none]</td>';
    }
    
    $markup .= "<td>$status</td>";
    $markup .= "<td>$hours</td>";
    $eventDate = date(TIME_FORMAT, dbDateSQL2PHP($outreach['logDate']));
    $markup .= "<td>$eventDate</td></tr>";
  }
  
  $markup .= '</table>';
  return array('#markup' => $markup);
}

?>