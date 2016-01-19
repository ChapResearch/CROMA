<?php

/*

---- outreach/williamDisplay.php ----

Used for display and creation/editing of outreach events.

- Contents -
viewUpcomingOutreach() - Displays the outreach sidebar with all team outreach events.
viewOutreachEvent() - Displays all information for a given outreach event.

*/

// viewUpcomingOutreach() - Displays the outreach sidebar with all team outreach events.

function viewUpcomingOutreach() 
{ 
  global $user;
  $markup = '<table id="01"><tr><th colspan="5" style="text-align: center"><b>CROMA - UPCOMING EVENTS</b></th></tr>';
  $outreaches = dbGetApprovedOutreachForUser($user->uid);
  
  foreach($outreaches as $outreach) {
    $markup .= '<tr><td colspan="1"><table>';
    $markup .='<tr><td><b>' . $outreach['TID'] . '</b></td></tr>' . '<tr><td><button type="button">Contribute</button></td></tr></table></td><td colspan="4">';
    $markup .='<table><tr><td><b>'.'<a href="http://croma.chapresearch.com/?q=viewOutreach&OID='.$outreach['OID'].'"target="_blank">'.chopString($outreach['name'],15) . '</a>' .  '</b></td></tr>';
    $markup .='<tr><td>' . chopString($outreach['logDate'],30) . '</td></tr>';
    $markup .='<tr><td>' . wordwrap(chopString($outreach['address'],30),15,"<br>\n",TRUE) . '</td></tr>';
    $markup .='<tr><td>' . wordwrap(chopString($outreach['description'],30),15,"<br>\n",TRUE) . '</td></tr>';
    $markup .='</table></td></tr>';
  }
  
   $markup .= '</table>';

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

// Displays all information for a given outreach event.

function viewOutreachEvent() {
  $params = drupal_get_query_parameters();

  if(isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
    $team = dbGetTeam($outreach['TID']);
    $markup = '<div align="left">' . "<br><h2><b>{$outreach['name']}</b></h2></div>";

    if($outreach['FID']!=null) {
      $FID = dbGetOutreachThumbnail($OID);
      $file = file_load($FID);
      $link = file_create_url($file->uri);
      $markup .= '<div align="right"><img src="' . $link . '" style="width:125px; height:125px;"></div><br>';
    }
    
    $markup .= '<div align="right">' . '<a href="http://croma.chapresearch.com/?q=logHours';
    $markup .= '&OID='. $OID . '">';
    $markup .= '<button type="button"';
    $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
    $markup .= '>Log Hours</button></a>';
    $markup .= '<a href="http://croma.chapresearch.com/?q=viewMedia';
    $markup .= '&OID='. $OID . '">';
    $markup .= '<button type="button"';
    $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
    $markup .= '>View Media</button></a>';
    $markup .= '<a href= "http://croma.chapresearch.com/?q=outreachForm';
    $markup .= '&OID='. $OID . '">';
    $markup .= '<button>Edit Event</button></a></div>';
    
    // Begin Displaying Info Body
    $markup .= '<table style="width:100%">';
    $markup .= '<tr> <td colspan="3"><b>Team:</b> ' . $team['name'];
    $markup .= '<td colspan="3"><b>Total Hours:</b> ' . dbGetHoursForOutreach($OID) . '</td></tr>';
    $markup .= '<td colspan="3"><b>Date:</b> ' .$outreach['logDate'] . '</td>';
    $markup .= '<td colspan="3"><b>Status: </b>';
    
    switch($outreach['status']) {
    case 'isOutreach': $markup .= 'Outreach'; break;
    case 'isIdea': $markup .= 'Idea'; break;
    case 'doingWriteUp': $markup .= 'Write-Up'; break;
    case 'Locked': $markup .= 'Locked'; break;
    }   
    
    $markup .= '</td></tr>';
    $markup .= '<tr><td colspan="6"><b>Description:</b> ' . $outreach['description'] . '</td></tr>';
    $markup .= '<tr><td colspan="6"><b>Address:</b> ' . $outreach['city'] . '</td></tr>';
    $markup .= '<tr><td colspan="6"><b>Contact Name:</b> ' . $outreach['co_firstName'] . ' ' . $outreach['co_lastName'] . '</td></tr>';
    $markup .= '<tr><td colspan="3"><b>Contact Email:</b> ' . $outreach['co_email'] . '</td>';
    $markup .= '<td colspan="3"><b>Contact Number:</b> ' . $outreach['co_phoneNumber'] . '</td></tr>';
    $markup .= '<tr><td colspan="3"><b>People Reached:</b> ' . $outreach['peopleImpacted'] . '</td>';
    $markup .= '<td colspan="3"><b>Type:</b> ' .$outreach['type'] . '</td></tr>';
    $markup .= '</table>';
    $retArray = array();
    $retArray['#markup'] = $markup;
    return $retArray;
  } else {
    drupal_set_message('Invalid outreach event. Click <a href="http://croma.chapresearch.com/?q=viewTeamOutreach">here</a> to return to event selection.', 'error');
  }
}

?>