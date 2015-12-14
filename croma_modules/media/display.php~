<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
//include_once("/var/www-croma/database/croma_dbFunctions.php");

$blocks = array(array("id" => "incoming_media_view", "title" => "CROMA - Incoming Media", "content" => "viewIncomingMedia")
 ); 

global $mediaBlockInfo;
global $mediaBlockViewFns;

blockLoadOther($mediaBlockInfo,$mediaBlockViewFns,$blocks);  

/* 

*/
function viewIncomingMedia() 
{ 
  $medias = dbGetIncomingMediaForUser("1");
  $markup = 'Incoming media!<br>';
  $markup .='<div align="right"><button type="button">All Media</button></div>';  
  $markup .='<table id="t01">';
  $date= date('Y-m-d');

  foreach($medias as $media){
    $markup .='<tr><td style = "vertical-align: middle;"><img src="' . $media["link"] . '" height="50" width="50"></td>';
    $markup .='<td style = "vertical-align: middle;">' . $media["title"] .'<br>' . $date .'<br>' . $media["description"] . '</td>';
    $markup .='<td style = "vertical-align: middle;"><button type="button">Assign</button></td>';
    $markup .='</tr>';    
  }
  
  $markup .='</table>';
  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

?>