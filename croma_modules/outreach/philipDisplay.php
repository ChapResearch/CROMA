<?php
/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach with the status of idea.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");

// allows a user to view outreaches with the status of idea

function viewOutreachIdeas() 
{ 
  $markup = "View Outreach Ideas";
  $outreaches = dbGetOutreachIdeas("1"); /* allows a user to see the outreach ideas of the team currently being used, these events are ideas when specified in the outreach form */
  $markup .="<table>";

  $markup .= "<tr>";
  $markup .= "<th>Name</th>";
  $markup .= "<th>Description</th>";
  $markup .= "<th>Type</th>:";
  $markup .= "</tr>";

  foreach($outreaches as $outreach){
    $markup .= "<tr>";
    $markup .= "<td>" . $outreach["name"]."</td>";
    $markup .= "<td>" . $outreach["description"]."</td>";
    $markup .= "<td>" . $outreach["type"]."</td>";
    $markup .= "</tr>";
}  
  $markup .="</table>";
  
  // TODO - add a cancel button

  $retArray = array();
  $retArray['#markup'] = $markup;
  
  return $retArray;
}



?>