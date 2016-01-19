<?php


ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once ("/var/www-croma/database/croma_dbFunctions.php");

function viewTeamOutreach()
{
  global $user;
  $params = drupal_get_query_parameters();


  
  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
    $teamName = $team['name'];
  } else {
    $teams = dbGetTeamsForUser($user->uid);
    $TID = $teams[0]['TID'];
    $teamName = $teams[0]['name'];
  }
  
  $markup = "<h3>Showing outreach for $teamName</h3>";
  $outreaches = dbGetOutreachesForTeam($TID);
  foreach ($outreaches as &$outreach){
    $outreach['hours'] = dbGetHoursForOutreach($outreach["OID"]);
  }

  unset($outreach); 
  $orderParam = isset($params["sort"])?$params['sort']:'name';
  $isAscending = isset($params["isAscending"]);
  orderByValue($outreaches, $orderParam, $isAscending); // custom function (see helperFunctions.inc)
  $showAll = isset($params["showAll"]);

  // Begin Displaying Outreach Events
  $markup .= '<div style="float:left; width:70%;"><div style="float:left; width:50%;"><h2>Outreach Events</h2></div>';
  $markup .= '<div align="right" style="float:right; width:50%;"><h2><a href="?q=outreachForm"><button>Add Outreach</button></a></h2>';
  $showText = 'Show All Outreaches';
  $hideText = 'Hide Outreach Ideas';
  $markup .= '<h2>' . showAllButton('teamDashboard', $orderParam, $isAscending, $showAll, $showText, $hideText);
  $markup .= '</h2></div>';
  $markup .= '<table><tr><td>' . sortHeader($orderParam, $showAll, $isAscending, "Name", "name", "teamDashboard") . '</td>';
  $markup .= '<td>Description</td>';
  $markup .= '<td>' . sortHeader($orderParam, $showAll, $isAscending, "Type", "status", "teamDashboard") . '</td>';
  $markup .= '<td>' . sortHeader($orderParam, $showAll, $isAscending, "Hours", "hours", "teamDashboard") . '</td></tr>';

  
  foreach($outreaches as $outreach)  {
    if($outreach["status"] == "isOutreach" || ($outreach["status"] == "isIdea" && $showAll == true)) {
      if($outreach["status"] == "isOutreach"){
	$status = "Outreach";
      } else  {
	$status = "Idea";
      }
      
      $markup .= '<tr><td style = "vertical-align: middle;"><a href="http://croma.chapresearch.com/?q=viewOutreach&OID=' .$outreach["OID"] . '"</a>'; 
      $markup .= $outreach["name"] . '</td>';
      $markup .='<td style = "vertical-align: middle;">' . $outreach["description"] . '</td>';
      $markup .='<td style = "vertical-align: middle;">' . $status . '</td>';
      $markup .='<td style = "vertical-align: middle;">' . $outreach['hours'];
      $markup .='</tr>';
    }
    
    // TODO - combine this whole section into just one if statement
    
    
  }
  
  $markup .= '</table></div>';
  
  //Begin Displaying Members
  $params = drupal_get_query_parameters();
  $team = dbGetTeam($TID);
  $markup .= '<div style="float:right; width:30%;"><h2>Members</h2><table>';
  $profiles = dbGetUsersFromTeam($TID);

  //Sets up the table to display the name, role, and grade of every user of the certain team.
  $markup .= '<tr><td><b>Name</b></td>';
  $markup .= '<td><b>Grade</b></td></tr>';
  
  foreach($profiles as $profile1) {
    $markup .= '<tr><td><a href="http://croma.chapresearch.com/?q=viewUser&UID=' . $profile1["UID"] . ' "  target="_blank">' . $profile1["firstName"] . " " . $profile1["lastName"]. '</a></td>'; //Hyperlinks the name so every name is linked to it's user profile.
    $markup .= '<td>' . $profile1["grade"] . '</td></tr>'; 
  }
  
  $markup .= '</table></div>';
  
  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

?>