<?php

/*
  ---- outreach/viewOutreachEvent.php ----

  used for display of an outreach event

  - Contents -
  viewOutreachEvent - displays all information for a given outreach event
*/

// viewOutreachEvent - Displays all information for a given outreach event.
function viewOutreachEvent() 
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  if (isset($params['OID']) && $params['OID'] > 0) {
    $OID = $params['OID'];
    $outreach = dbGetOutreach($OID);
    if ($outreach == false) {
      drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
      return;
    }

    $TID = $outreach['TID'];
    if (!isMyTeam($TID)){
      drupal_set_message('You do not have permission to access this page.', 'error');
      return;
    }      

    // if the outreach status is outreach and the event is over, then turn the status to write up
    if ($outreach['status'] == "isOutreach") {
      outreachToWriteUp($OID);
    }
    
    // determine if the user can physically sign up
    $canSignUp = (!dbIsOutreachOver($OID) 
		  && ($outreach['status'] == 'isOutreach' || $outreach['status'] == 'doingWriteUp')); 

    $markup = '';
    $markup .= '<div style="float:left; width:38%">';
    $markup .= '<table style="margin:0px 0px 10px 0px;"><tr>';
    $markup .= '<td style="padding:0px 14px 10px 14px;"><div align="left"><h2 style="margin:0px 0px 7px 0px;"><b>';
    // display outreach name
    $markup .= "{$outreach['name']}";
    $markup .= '</b></h2></div></td></tr>';

    $markup .= '<tr><td>';    

    $markup .= showOutreachStatusIcon($outreach['status']);

    // displays the icon for a public outreach
    $markup .= $outreach['isPublic'] ? '<span title="Public"><img class="eventPrivacyIcon" src="/images/icons/publicBlue.png"></span>' : '<span title="Private"><img class="eventPrivacyIcon" src="/images/icons/privateBlue.png"></span>';
    
    // displays the icon for a cancelled outreach
    $markup .= $outreach['cancelled'] ? '<span title="Event Cancelled"><img class="eventCancelledIcon" src="/images/icons/cancelledRed.png"':'';

    $markup .= '</td></tr></table>';
    $markup .= '<table id="photoAndEdit"><tr><td style="padding:0px;">';  

    // cannot edit photo if user doesn't have the correct permissions
    if (!isMyOutreach($OID) && !hasPermissionForTeam('editAnyOutreach',getCurrentTeam()['TID'])){
      $markup .= '<div align="right">';
      $markup .= '<span title="Edit Photo"><button type="button" disabled><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></span>';
      $markup .='</div>';
    } else {
    // edit photo if user has permissions
      $markup .= '<div align="right">';
      $markup .= '<a href= "?q=editThumbnail';
      $markup .= '&OID='. $OID . '&FID=' . $outreach['FID'] . '">';
      $markup .= '<span title="Edit Photo"><button type="button"><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></a></span>';
      $markup .='</div>';
    }

    $markup .= '</td></tr><tr><td style="padding:0px;">';
    // default picture for outreach
    if (!empty($outreach['FID'])) {
      $FID = dbGetOutreachThumbnail($OID);
      $url = generateURL($FID);
      $markup .= '<div align="center"><img src="' . $url . '" style="max-width:150px; width:auto; height:auto; padding: 5px 0px 5px 0px">';
    } else {
      $markup .= '<div align="center"><img src="/images/defaultPics/team.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">';
    }
    
    $markup .= '</div></td></tr></table></div>';
    $markup .= '<div align="right">';

    // if the status is write-up, then allow a user to submit a write up
    if ($outreach['status'] == 'doingWriteUp' && !$outreach['isWriteUpSubmitted']){
      $markup .= '<a href="?q=writeupform&OID=' .$outreach['OID']. '"><button>Write Up</button></a>';
    } else if ($outreach['isWriteUpSubmitted'] 
	       && hasPermissionForTeam('approveIdeas', $TID) 
	       && $outreach['status'] == 'doingWriteUp'){
      $markup .= '<a href="?q=writeupform&OID=' .$outreach['OID']. '&approving"><button>Approve Write Up</button></a>';
    }

    // if the status is idea, then allow a user with permissions to approve or reject the idea
    if ($outreach['status'] == 'isIdea' && hasPermissionForTeam('approveIdeas', $TID)){
      $markup .= '<a href="?q=approveIdea/' .$outreach['OID'] . '/'. $TID. '"><button>Approve</button></a>';
      $markup .= '<a href="?q=rejectIdea/' .$outreach['OID'] . '/'. $TID. '"><button>Reject</button></a>';
    }

    // notifications button
    if (!isMyOutreach($OID) && !hasPermissionForTeam('editAnyOutreach',getCurrentTeam()['TID'])){
      $markup .= '<button type="button" disabled>Notifications</button>';      
    } else {
      $markup .= '<a href="?q=manageNotifications&OID='.$outreach['OID'].'"><button>Notifications</button></a>';
    }

    // manage sign-ups button
    if (!dbIsOutreachCancelled($OID)){
      if (dbIsUserSignedUp($UID, $OID)){
	if (dbIsOutreachOver($OID)){
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Edit Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot edit your sign up for this event because it is already over.</span></div></a>';
	}
	else{
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button">Edit Sign Up</button><span id="helptext"; class="helptext tooltiptext4">Click here to edit your sign up for this event.</span></div></a>';
	}
      } else{
	if (dbIsOutreachOver($OID) || $outreach['status'] == 'isIdea'){
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot sign up for this event because it is already over.</span></div></a>';
	}
	else{
	  $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button">Sign Up</button><span id="helptext"; class="helptext tooltiptext4">Click here to sign up for this event.</span></div></a>';
	}
      }
    }else{
      $markup .= '<a href="?q=signUp&OID=' . $OID . '"><div class="help tooltip4"><button type="button" disabled>Sign Up</button><span id="helptext"; class="helptext tooltiptext4">You cannot sign up for this event because it is cancelled.</span></div></a>';
    }

    // hours button
      if (!dbIsOutreachCancelled($OID)){
	$markup .= '<a href= "?q=viewHours';
	$markup .= '&OID='. $OID . '">';
	$markup .= '<button type="button" ';
	$markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
	$markup .='>Hours</button></a>';
      } else { // if outreach is cancelled
	$markup .= '<button type="button" disabled';
	$markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
	$markup .='>Hours</button>';
      }

      // view media button
      $markup .= '<a href="?q=viewMedia';
      $markup .= '&OID='. $OID . '">';
      $markup .= '<button type="button"';
      $markup .= $outreach['status'] == 'isIdea' ? ' disabled' : '';
      $markup .= '>Media</button></a>';

      // edit outreach button
      if (!isMyOutreach($OID) && !hasPermissionForTeam('editAnyOutreach',getCurrentTeam()['TID'])){
	$markup .= '<button type="button" disabled><img class="editIcon" src="/images/icons/editWhite.png"></button>';
      } else {
	$markup .= '<a href= "?q=outreachForm';
	$markup .= '&OID='. $OID . '">';
	$markup .= '<button type="button"><img class="editIcon" src="/images/icons/editWhite.png"></button></a>';
      }
      $markup .='</div>';

      $markup .= '<div style="width:60%; float:right; padding-left:10px">';

      $hasPointOfContact = false;
      if (!(empty($outreach['co_organization']) && empty($outreach['co_firstName']) && empty($outreach['co_email']) && empty($outreach['co_phoneNumber']))){
	$hasPointOfContact = true;
      }

      // account for cases where no info is present
      if ($outreach['description'] == null) $outreach['description'] = '[none]';
      if ($outreach['type'] == null || $outreach['type'] == '' ) $outreach['type'] = '[none]';
      if ($outreach['status'] == null) $outreach['status'] = '[none]';
      if ($outreach['co_organization'] == null) $outreach['co_organization'] = '[none]';
      if ($outreach['co_position'] == null) $outreach['co_position'] = '[none]';
      if ($outreach['co_firstName'] == null) $outreach['co_firstName'] = '[none]';
      if ($outreach['co_email'] == null) $outreach['co_email'] = '[none]';
      if ($outreach['co_phoneNumber'] == null) $outreach['co_phoneNumber'] = '[none]';
      if ($outreach['city'] == null) $outreach['city'] = '[none]';
      if ($outreach['state'] == null) $outreach['state'] = '[none]';
      if ($outreach['address'] == null) $outreach['address'] = '[none]';
      if ($outreach['country'] == null) $outreach['country'] = '[none]';
      if ($outreach['totalAttendance'] == null) $outreach['totalAttendance'] = 0;
      if ($outreach['testimonial'] == null) $outreach['testimonial'] = '[none]';

      $team = dbGetTeam($outreach['TID']);
    
      // begin displaying info body

      $markup .= '<table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><h3><b><u>General<u></b></h3></td></tr>';
      $owner = dbGetOutreachOwner($OID);
      $markup .= "<tr><td colspan='3'><b>Owner: </b>" . dbGetUserName($owner) . "</a></td>";
      $markup .= "<td colspan='3'><b>Team: </b>{$team['number']}</td></tr>";
    
      $markup .= '<tr><td colspan="3"><b>Tags: </b>';
      $tags = dbGetTagsForOutreach($OID);
      if (!empty($tags)){
	dpm($tags);
	$first = true;
	$length = count($tags);
	$i = 1;
	foreach($tags as $OTID => $tagName){
	  $markup .= '<a href="?q=outreach&tag=' . $OTID . '">' . $tagName . '</a>';
	  if ($i < $length){
	    $markup .= ', ';
	  }
	  $i++;

	}
	// if there aren't any tags
      } else {
	$markup .= '[none]';
      }
      $markup .= '</td></tr>';

      $times = dbGetTimesForOutreach($OID);

      // display time if the outreach status isn't an idea
      if ($outreach['status'] != 'isIdea'){
	if (!empty($times)){
	  foreach($times as $time){
	    $startTime = date(TIME_FORMAT, dbDateSQL2PHP($time['startTime']));
	    $endTime = date(TIME_FORMAT, dbDateSQL2PHP($time['endTime']));
	    $markup .= '<tr><td colspan="3"><b>Start Date: </b>' . $startTime . '</td>';
	    $markup .= '<td colspan="3"><b>End Date: </b>' . $endTime . '</td></tr>';
	  }
	}
      }

      $markup .= '<tr><td colspan="5" style="word-break:break-word"><b>Description: </b>';

      $markup .= wordwrap($outreach['description'], 70, "<br />\n");

      $markup .= '</td></tr>';

      // if the outreach has contact information
      if ($hasPointOfContact){
	$markup .= '<tr><td><h3><b><u>Contact Info<u></b></h3></td></tr>';

	$markup .= '<tr><td colspan="3"><b>Host Organization: </b>';
	$markup .= strip_tags($outreach['co_organization'], ALLOWED_TAGS) . '</td>';
	$markup .= '<td colspan="3"><b>Contact Name: </b>';
	$markup .= strip_tags($outreach['co_firstName'].' '.$outreach['co_lastName'], ALLOWED_TAGS) . '</td></tr>';
	$markup .= '<tr><td colspan="3"><b>Contact Email: </b>'.strip_tags($outreach['co_email'], ALLOWED_TAGS).'</td>';
	$phoneNumber = dbFormatPhoneNumber($outreach['co_phoneNumber']);
	$markup .= '<td colspan="3"><b>Contact Number: </b>' . $phoneNumber . '</td></tr>';
	$markup .= '<tr><td colspan="6"><b>Address: </b>'.strip_tags($outreach['address'], ALLOWED_TAGS).', '.strip_tags($outreach['city'], ALLOWED_TAGS).', '.strip_tags($outreach['state'], ALLOWED_TAGS).'</td></tr>';
	$markup .= '</tr>';
      } else {

	$markup .= '<tr><td><h3><b><u>Contact Info<u></b></h3></td></tr>';

	$markup .= '<tr><td colspan="6"><b>Address: </b>'.strip_tags($outreach['address'], ALLOWED_TAGS).', '.strip_tags($outreach['city'], ALLOWED_TAGS).', '.strip_tags($outreach['state'], ALLOWED_TAGS).'</td></tr>';
	$markup .= '</tr>';
      }

      $markup .= '<tr><td><h3><b><u>Statistics<u></b></h3></td></tr>';
      $markup .= '<tr>';
    
      if ($outreach['status'] != 'isIdea'){
	$numPpl = dbGetNumPplSignedUpForEvent($OID);
	$markup .= '<td colspan="3"><b>';
	// only show the link if people are signed up
	if ($numPpl != 0){
	  $markup .= '<a href="?q=outreachList&OID='. $OID .'"target="_blank">';
	}
	$markup .= 'People Signed Up: </b>';
	// end the link
	if ($numPpl != 0){$markup .= '</a>';} 
	$markup .= $numPpl . '</td>';

	// view total hours for the outreach
	$markup .= '<td colspan="3"><b>Total Hours: </b><a href="?q=viewHours&OID='.$OID.'">' . dbGetHoursForOutreach($OID) . '</a></td></tr>';
	//if the outreach status is idea
      } else {
	$markup .= '<td colspan="3">';
	$markup .= '<b>People Signed Up: </b>';
	$markup.= 'None';
	$markup .= '</td></tr>';
      }
      
      $markup .= '</table></div>';
      // if the outreach has an approved write-up
      if ($outreach['isWriteUpApproved'] && $outreach['status'] == 'locked'){
	$writeUp = empty($outreach["writeUp"])?'[None]':$outreach["writeUp"];
	$totalAttendance = empty($outreach["totalAttendance"])?'[Not Filled Out]':$outreach["totalAttendance"];
	$testimonial = empty($outreach["testimonial"])?'[None]':$outreach["testimonial"];
	$markup .= '<div style="float:left; width:38%;"><table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><h3><b><u>Write Up<u></b></h3></td>';
	$markup .= '<td><a href="?q=writeupform&OID=' .$outreach['OID']. '&approved"><button> View</button></a></td></tr>';
	$markup .= '<tr><td><b>Write Up:</b></td></tr>';
	$markup .= '<tr><td>'. $writeUp .'</td></tr>';
	$markup .= '<tr><td><b>Total Attendance:</b></td></tr>';
	$markup .= '<tr><td>'. $totalAttendance .'</td></tr>';
	$markup .= '<tr><td><b>Testimonials/Comments:</b></td></tr>';
	$markup .= '<tr><td>'. $testimonial .'</td></tr>';
	$markup .= '</table></div>';
      }

      $retArray = array();
      $retArray['#markup'] = $markup;
      return $retArray;
  } else {
    drupal_set_message('Invalid outreach event. Click <a href="?q=teamDashboard">here</a> to navigate back to events in Team Dashboard.', 'error');
  }
}

?>