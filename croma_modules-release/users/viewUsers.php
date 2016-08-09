<?php

/*
  ---- users/viewUsers.php ----

  this file creates blocks that implement displaying of a user

  - Contents -
  viewUser() - allows a user to view their personal information from the users table in the database
  myDashboardHeader() - used for switching teams on my dashboard (not used though)
  viewUserProfileSummary() - used on my dashboard to view user and view user teams
*/   

//  viewUser() - allows a user to view their personal information from the users table in the database
function viewUser()
{
  global $user;
  $currentUID = $user->uid;
  $params = drupal_get_query_parameters();

  // checks that there is a user
  if(isset($params["UID"])) {
    $UID = $params["UID"];
  } else {
    drupal_set_message('No user specified.', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // checks that the user being viewed shares a team with the user currently viewing
  if(!($UID == $currentUID || isOnMyTeam($UID))){
    drupal_set_message("You can't view this profile.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $profile = dbGetUserProfile($UID);

  $canEdit = false;
  $sharedTeams = getSharedTeams($UID);
  if(!empty($sharedTeams)){
    foreach($sharedTeams as $TID){
      if (hasPermissionForTeam('manageTeamMembers', $TID)){
	$canEdit = true;
	break;
      }
    }
  }

  if($user->uid == $UID){
    $canEdit = true;
  }

  $markup = '';
  // create name header and table
  $markup .= '<div style="float:left; width:28%">';
  $markup .= '<table style="margin:0px 0px 10px 0px;"><tr>';

  $markup .= '<td style="padding:0px 14px 10px 14px;"><div align="left"><h2 style="margin:0px 0px 7px 0px;"><b>';
  $markup .= $profile['firstName'] . ' ' . $profile['lastName'];
  $markup .= '</b></h2></div></td></tr></table>';

  $markup .= '<table id="photoAndEdit"><tr><td style="padding:0px;">';  

  // if the profile belongs to the currently logged in user, the picture should be editable
  if($canEdit){
    $markup .= '<div align="right">';
    $markup .= '<a href= "?q=editThumbnail';
    $markup .= '&UID='. $UID . '&FID=' . $profile['FID'].'">';
    $markup .= '<span title="Edit Photo"><button type="button"><img class="editIcon" src="/images/icons/editThumbnailWhite.png"></button></a></span>';
    $markup .='</div>';
  }

  $markup .= '</td></tr><tr><td style="padding:0px;">';

  // if user has picture, display picture
  if(!empty($profile['FID'])) {
    $url = generateURL($profile['FID']);
    $markup .= '<div align="center"><img src="' . $url . '" style="max-width:150px; width:auto; height:auto; padding: 5px 0px 5px 0px">';
    // default picture if user does not have a picture
  } else {
    $markup .= '<div align="center"><img src="/images/defaultPics/user.png" style="max-width:200px; width:auto; height:auto; padding: 15px 0px 15px 0px">';
  }

  $markup .= '</div></td></tr></table></div>';
  $markup .= '<div align="right">';
  
  // if user has permissions or owns the profile, edit info
  if($canEdit){
    $markup .= '<a href= "?q=profileForm';
    $markup .= '&UID='. $UID . '">';
    $markup .= '<span title="Edit Profile"><button type="button"><img class="editIcon" src="/images/icons/editWhite.png"></button></a></span>';
  }

  // if the user wants to change their own password - no one else can access this feature on someone's profile
  if($UID == $currentUID){
    $markup .= '<a href="?q=user/' . $UID . '/edit"';
    $markup .= '<span title="Change Password"><button type="button"><img class="keyIcon" src="/images/icons/keyWhite.png"></button></a></span>';
  }
  
  // users are only allowed to delete their own profiles
  if ($UID == $currentUID){
    $markup .='<span title="Delete User"><a href="?q=deleteUser&UID=' . $UID . '"><button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></span>';
  }

  $markup .= '</div>';

  // begin displaying info portion

  $markup .= '<div style="width:70%; float:right; padding-left:10px">';
  $markup .= '<table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><b>Role: </b>'.ucfirst($profile['type']) . '</td>'; 
  $markup .= '<td><b>Position: </b> ' . strip_tags($profile['position']) . '</td>';

  if($profile['grade'] == '0'){
    $markup .= '<tr><td><b>Grade: </b> N/A</td>';
  }else{
    $markup .= '<tr><td><b>Grade: </b> ' . $profile['grade'] . '</td>';
  }

  $markup .= '<td><b>Gender: </b> ' . $profile['gender'] . '</td></tr>';
  $email = dbGetUserPrimaryEmail($UID);
  $markup .= '<tr><td><b>Email: </b> <a href="mailto:' . $email . '" target="_blank">';
  $markup .= $email . '</a>';
  $secondaryEmail = dbGetSecondaryEmailForUser($UID);
  if($secondaryEmail){
    $markup .= "<br>(" . '<a href="mailto:' . $secondaryEmail . '" target="_blank">' .  $secondaryEmail . '</a>' . ')';
  }
  
  $phoneNumber = dbFormatPhoneNumber($profile['phone']);
  $markup .= '</td><td><b>Phone: </b> ' . $phoneNumber . '</td></tr>';

  // displays teams the user is on

  $teamNumbers = '';
  $first = true; // used to put commas in the right places
  $teams = dbGetTeamsForUser($UID);
  
  foreach($teams as $team) {
    if($first){
      $teamNumbers = '<a href="?q=viewTeam&TID=' . $team['TID'] . '">' . $team['number'] . '</a>';
      $first = false;
    } else {
      $teamNumbers = $teamNumbers . ', <a href="?q=viewTeam&TID=' . $team['TID'] . '">' . $team['number'] . '</a>';
    }
  }
  
  if(count($teams) > 1){
    $teamLabel = 'Teams';
  } else {
    $teamLabel = 'Team';
  }
  
  if ($UID == $currentUID){
    $markup .= '<tr><td><a href="?q=manageUserTeams"><b>' . $teamLabel . ':</b></a> ' . $teamNumbers . '</td>';
  }else{
    $markup .= '<tr><td><b>' . $teamLabel . ':</b> ' . $teamNumbers . '</td>';
  }

  // displays user hours
  $numberOfHours = dbGetUserHours($UID);

  if($numberOfHours != 0){
    $markup .= "<td><a href=\"?q=viewHours&UID=$UID\"><b>Number of Hours:</b></a> $numberOfHours</td>";
  } else {
    $markup .= "<td><b>Number of Hours:</b> No Hours!</td>"; 
  }

  // displays user bio
  $markup .= '</table><table id="miniViewTeam" style="margin:16px 0px 0px 0px"><tr><td><b>Bio: </b>';
  $markup .= wordwrap($profile['bio'], 92, "<br />\n") . '</td></tr>';

  $markup .= '</table></div>';

  return array("#markup"=>$markup);
}

// viewUserProfileSummary() - used on my dashboard to view brief profile data
function viewUserProfileSummary()
{
  global $user;
  $params = drupal_get_query_parameters();

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  $profile = dbGetUserProfile($UID);

  // create table, display user name
  $markup = '<table id="miniViewUser" style="margin:102px 0px 0px 0px"><tr><td colspan="6" style="text-align:center"><h2><b>' . $profile['firstName'] . ' ' . $profile['lastName'] . '</b></h2></td>';
  $markup .='<tr><td colspan="6" style="text-align:center">';

  // display picture if user has one
  if(!empty($profile['FID'])) {
    $FID = $profile['FID'];
    $file = file_load($FID);
    $uri = $file->uri;
    $variables = array('style_name'=>'profile','path'=>$uri,'width'=>'150','height'=>'150');
    $image = theme_image_style($variables);
    $markup .= $image;
  } else {    // default picture displayed
    $markup .= '<img src="/images/defaultPics/user.png">';
    }

  $markup .='</td></tr><tr>';

  // my profile button to view full user profile
  $markup .= '<td colspan="3" style="text-align:left"><a href="?q=viewUser';
  $markup .= '&UID=' . $UID . '">';
  $markup .= '<div class="help tooltip4"><button type="button">My Profile</button>';

  // my teams button to manage teams for user
  $markup .= '<span id="helptext"; class="helptext tooltiptext4">';
  $markup .= 'Click here to view/edit your user profile and to manage your teams.';
  $markup .= '</span></div></a>';

  $markup .= '</td>';

  $markup .= '<td colspan="3" style="text-align:right"><a href= "?q=manageUserTeams">';
  $markup .= '<div class="help tooltip3"><button type="button">My Teams</button><span id="helptext"; class="helptext tooltiptext3">Click here to create, apply to, or leave a team.</span></div></a></td>';  

  $markup .= '</tr></div></table>';
  
  return array('#markup' => $markup);
}

?> 