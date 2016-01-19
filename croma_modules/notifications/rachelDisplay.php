<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

/* 
   viewNotifications() - show all notifications for the given user. 
*/
function viewNotifications() 
{ 
  global $user;

  $notifications = dbGetNotificationsForUser($user->uid);

  if(empty($notifications)){
    $markup = 'No notifications!';
  } else {
    $markup ='<table>';

    foreach ($notifications as $notification){
      $markup .="<tr>";
 
      if ($notification['FID'] != null){
	$pic = file_load($notification['FID']);
	$url = file_create_url($pic->uri);
	$markup .='<td><img src="' . $url . '" height="50" width="50"></td>';
      }

      $markup .='<td><table style="float:right">';
      $markup .='<td><button type="button">Dismiss</button></td>';
      $markup .='</tr></table>';
      $markup .=$notification['date'] . "<br>" .  $notification['title'] . "<br>" . $notification['message'];

      $markup .="</td></tr>";

    }

    $markup .='</table>';

  }

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

?>