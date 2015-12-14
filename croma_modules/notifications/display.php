<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");

$blocks = array(array("id" => "notifications_view", "title" => "CROMA - View Notifications", "content" => "viewNotifications"));

global $notificationsBlockInfo;
global $notificationsBlockViewFns;

blockLoadOther($notificationsBlockInfo,$notificationsBlockViewFns,$blocks);  

/* 
   viewNotifications() - show all notifications for the given user. 
*/
function viewNotifications() 
{ 
  $notifications = dbGetNotificationsForUser("1");
  $markup = 'Notifications!<br>';

  $markup .='<table id="t01">';

  foreach ($notifications as $notification){
    //    $markup .= "Name: " . $notification["title"] . "<br>";
    //    $markup .= "Date: " . $notification["date"] . "<br>";
    //    $markup .= "Description: " . $notification["message"] . "<br>";

 $markup .="<tr>";

 $markup .='<td><img src="' . $notification['link'] . '" height="50" width="50"></td>';

 $markup .='<td><table id="t02" style="float:right">';
     $markup .='<td><button type="button">Dismiss</button></td>';
     $markup .='</tr></table>';
     $markup .=$notification['date'] . "<br>" .  $notification['title'] . "<br>" . $notification['message'];


 $markup .="</td></tr>";

  }

 $markup .='</table>';
  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

?>