<?php

/*
  ---- notification/notificationMenu.php ----

  generates the menu bar notification

  - Contents -
  menuBarNotification() - shows how many notifications the current user has
*/

function menuBarNotification()
{
  global $user;
  dbGetNotificationsForUser($user->uid);

  // displaying the number of unread notifications that the user has inside of a box which is displayed in the CROMA menu bar
  $markup = '<a href="?q=viewAllNotifications" style="color:#ffffff"><div class="tooltip" style="vertical-align:center; color:#ffffff; background-color:#8e2115; padding:0px 5px 0px 5px; margin: 4px 0px 0px 0px; border-style:solid; border-color:#ffffff; border-width:2px 2px 2px 2px">' . ($num = dbGetNumNotificationsForUser($user->uid)); 

  // displaying what the "number in the box" means via a span class --> this shows up when you hover over the "box"
  $markup .= '<span class="tooltiptext">' . $num;
  $markup .= $num == 1 ? ' notification' : ' notifications';
  $markup .= '</span></div></a>';
  return array('#markup'=>$markup);
}

?>