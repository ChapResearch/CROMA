<?php

/*
  ---- notifications/viewNotifications.php ----
  used for viewing and deleting of notifications.

  - Contents -
  dismissNotification() - Deletes notification given by $NID.
  dismissAllNotifications() - Deletes all notifications.
  viewNotifications() - Shows three notifications for the given user. 
  viewAllNotifications() - Shows all notifications for the given user.
*/

// dismissNotification() - Deletes notification given by $NID.
function dismissNotification($NID)
{
  $params = drupal_get_query_parameters();
  dbDeleteNotification($NID);
  drupal_set_message('Notification has been dismissed.');

  // go back to the "View All Notifications" page
  if(isset($params["allnote"])){
    drupal_goto('viewAllNotifications');
  } else {
    if(isset($_SERVER['HTTP_REFERER'])){
      drupal_goto($_SERVER['HTTP_REFERER']);
    } else {
      drupal_goto('myDashboard');
    }
  }
}

// dismissAllNotifications() - Deletes all notifications.
function dismissAllNotifications()
{
  global $user;

  if(!empty($_SESSION['notificationNIDsShown'])){
    foreach($_SESSION['notificationNIDsShown'] as $NID){ // used to ensure no undisplayed notifications are deleted
      dbDeleteNotification($NID);
    }
    drupal_set_message('All notifications have been dismissed.');
  } else {
    drupal_set_message('No notifications to dismiss!');
  }

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('myDashboard');
  }
}

// viewNotifications() - Shows all notifications for the given user. 

function viewNotifications() 
{ 
  global $user;
  $UID = $user->uid;

  // creates header, table, and view all notifications button
  $timezone =  $user->timezone;
  $notifications = dbGetNotificationsForUser($user->uid, NOTIFICATION_PREVIEW_NUMBER);
  $markup = '<table><tr><td style="text-align:left"><h2><b>Notifications</b></h2></td>';
  $markup .= '<td style="text-align:right"><a href="?q=viewAllNotifications"><div class="help tooltip1"><button>';
  $markup .= 'View All</button></a>';

  $markup .= '<span id="helptext"; class="helptext tooltiptext1">';
  $markup .= 'Click here to view or to dismiss all visible notifications.';
  $markup .= '</span></div>';

  $markup .= '</td></tr></table>';


  // if there are no notifications for a user
  if(empty($notifications)){
    $markup .= '<center><h4>No New Notifications! &#9786</h4></center>';
    $_SESSION['notificationNIDsShown'] = array();
  } else {    // if there are notifications for a user
    $markup .= '<table>';

    foreach ($notifications as $notification){
      $_SESSION['notificationNIDsShown'][] = $notification['NID']; // used to prevent lack of page refresh issues
      $rawDate = $notification['dateTargeted'];
      $rawDate = dbDateSQL2PHP($rawDate);
      $date =  date("m-d h:i A", $rawDate);
      $markup .= "<tr>";

      $markup .= '<td colspan="3" .  style=align:left>';

      if(!empty($notification['FID'])){
	$FID = $notification['FID'];
	$file = file_load($FID);
	$uri = $file->uri;
	$variables = array('style_name'=>'notifications','path'=>$uri,'width'=>'200','height'=>'200');
	$image = theme_image_style($variables);
	$markup .= $image;
      }

      $markup .= '</td>';

      $markup .= '<td colspan="3" . style="width:50% . "text-align:right">';
      $markup .= '<br><b>' . $date . "</b><br>";
      $markup .= '<i>' . $notification['message'] . '</i>';

      $markup .= '</td></tr>';
   
      // buttons for viewing or dismissing a notification
      $markup .= '<tr><td colspan="6"><div align="left">';

      if($notification['bttnLink'] != null && $notification['bttnTitle'] != null){
	$markup .= "<a href=\"{$notification['bttnLink']}\">";
	$markup .= '<button type="button">'.$notification['bttnTitle'].'</button>';
      }

      $markup .= '</div></td><td><div align="right">';

      $markup .= '<a href="?q=dismissNotification/'  . $notification['NID'] .'">';
      $markup .= '<button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a>';


      $markup .= '</div></td>';
      $markup .= '</tr>';

    }

    $markup .= '</table>';
  }

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

// viewAllNotifications() - Shows all notifications for the given user. 
function viewAllNotifications()
{
  global $user;
  $UID = $user->uid;

  // create page header, table, and dismiss all notifications button
  $timezone =  $user->timezone;
  $notifications = dbGetNotificationsForUser($user->uid);
  $markup = '<table><tr><td style="text-align:left"><h1><b>View All Notifications</b></h1></td>';
  $markup .= '<td style="text-align:right"><a href="?q=dismissAllNotifications"><button>';
  $markup .= 'Dismiss All</button></a></td></tr></table>';


  // if user has no notifications
  if(empty($notifications)){
    $markup .= '<table class="infoTable"><th></th><tr><td style="text-align:center" colspan="10"><em>No New Notifications! &#9786</em></td></tr></table>';
    $_SESSION['notificationNIDsShown'] = array();
  } else {    // if user has notifications
    $markup .= '<table class="infoTable"><tr><th>Associated Picture</th><th>Notification Content</th><th></th></tr>';

    foreach ($notifications as $notification){
      // used to prevent lack of page refresh issues
      $_SESSION['notificationNIDsShown'][] = $notification['NID']; 
      $rawDate = $notification['dateTargeted'];
      $rawDate = dbDateSQL2PHP($rawDate);
      $date =  date(TIME_FORMAT, $rawDate);
      $markup .= "<tr><td>";

      // generates picture for notification
      if(!empty($notification['FID'])){
	$FID = $notification['FID'];
	$file = file_load($FID);
	$uri = $file->uri;
	$variables = array('style_name'=>'all_notifications','path'=>$uri,'width'=>'150','height'=>'150');
	$image = theme_image_style($variables);
	$markup .= $image;
      }

      $markup .= '</td><td style="width:50% . "text-align:center">';
      $markup .= '<br><b>' . $date . "</b><br>";
      $markup .= '<i>' . $notification['message'] . "</i><br><br></td>";
      $markup .= '<td>';

      // buttons for viewing or dismissing a notification
      if($notification['bttnLink'] != null && $notification['bttnTitle'] != null){
	$markup .= "<a href=\"{$notification['bttnLink']}\">";
	$markup .= '<button type="button">'.$notification['bttnTitle'].'</button>';
      }

      $markup .= '<a href="?q=dismissNotification/'  . $notification['NID'] .'&allnote">';
      $markup .= '<button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a>' . '&nbsp' . '&nbsp';
      $markup .= '</td></tr>';
    }
    $markup .= '</td></tr></table>';
  }

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;

}

?>