<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");


function showUsersForTeam(){
  $params = drupal_get_query_parameters();
  $TID = 1;
  $team = dbGetTeam($params["TID"]);
  $markup = "<h3>" .  "<b>" . "Team Name: " .  $team["name"] . "</b>" . "</h3>";
  $markup .= '<table>';
  $users = dbGetUsersFromTeam($params["TID"]);
  dpm($users);
  $markup .= '<tr><td>' . "<b>Name</b>" .  '</td>';
  $markup .= '<td>' . "<b>Role</b>" . '</td>';
  $markup .= '<td>' . "<b>Grade</b>" . '</td></tr>';


  foreach($users as $user) {
    $markup .= '<tr><td>' . '<a href="http://croma.chapresearch.com/?q=viewUser&UID=' . $user["UID"] . ' "  target="_blank">' . $user["firstName"] . " " . $user["lastName"]. '</a>' . "</td>";
    $markup .= '<td>' . $user["position"] . '</td>';
    $markup .= '<td>' . $user["grade"] . '</td></tr>';
  }


  $markup .= '</table>';

  $array = array();
  $array['#markup'] = $markup;
  return $array;


}

?>