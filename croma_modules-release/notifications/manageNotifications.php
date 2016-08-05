<?php

/*
  ---- notifications/manageNotifications.php ----

  Used for managing notifications for an outreach.

  - Contents -
  manageNotifications() - add or delete notifications for an outreach event
*/

function manageNotifications()
{
  $params = drupal_get_query_parameters();

  if (isset($params['OID'])){
    $OID = $params['OID'];
    $outreachName = dbGetOutreachName($OID);

    // create page header, table, and add notification button
    $markup = '';
    $markup .= '<table>';
    $markup .= "<tr><td><h1>Manage Notifications for \"$outreachName\" </h1>";
    $markup .= '</td><td style="text-align:right">';
    $markup .= '<a href="?q=notificationForm&OID='.$OID.'"><button>Add Notification</button></a></td></tr></table>';

    $markup .= '<table class="infoTable"><tr><th colspan="3">Target Users</th><th colspan="3">Notification Message</th><th colspan="2">Date To Be Sent</th><th colspan="2"></th></tr>';

    $notifications = dbGetNotificationsForOutreach($OID);

    // if outreach already has notifications
    if (!empty($notifications)){

      foreach ($notifications as $notification){
	$markup .= '<tr>';
	$markup .= '<td colspan="3">'.dbGetUserName($notification['UID']).'</td>';
	$markup .= '<td colspan="3">'.$notification['message'].'</td>';
	$date = dbDateSQL2PHP($notification['dateTargeted']);
	$markup .= '<td colspan="2">'.date(TIME_FORMAT, $date).'</td>';
	$markup .= '<td colspan="2"><a href="?q=deleteNotification/'.$notification['NID'];
	$markup .= "/$OID\"><button>Delete</button></a></td>";
	$markup .= '</td>';
      }
      $markup .= '</table>';

    } else {      // if outreach does not have notifications
      $markup .= "<tr>";
      $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
      $markup .= "</tr>";
    }

    $markup .= "</table>";

    return array('#markup'=>$markup);

  } else {
    // returns message if OID is not correct
    drupal_set_message('No outreach selected!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
} 

?>