<?php

/*
  ---- media/viewMedia.php ----
  used for display and assigning of media items

  - Contents -
  viewIncomingMedia() - Displays information for all media associated with the user.
  viewPastUserMedia() - Displays media that the user has already assigned to outreach
  viewMedia() - Displays the page that shows all media for an outreach.
*/

// viewIncomingMedia() - Displays information for all media associated with the user.
function viewIncomingMedia() 
{ 
  global $user;
  $UID = $user->uid;

  if(dbGetTeamsForUser($user->uid) == false){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // processes the mail messages sent to pics@chapresearch.com (or its equivalent)
  getIncomingMedia(); 

  $medias = dbGetIncomingMediaForUser($UID);
  $markup = '<div class="help tooltip1"><h2 style="padding:96px 0px 0px 0px">Incoming Media</h2>';
  $markup .= '<span id="helptext"; class="helptext tooltiptext1">';
  $markup .= 'This is unassigned media that has been emailed to your CROMA account.';
  $markup .= '</span></div>';

  // create table
  $markup .= '<table class="infoTable"><tr><th>Media</th><th colspan="3">Info</th></tr>';
  
  // if user has no incoming media
  if(empty($medias)) {
    $markup .= '<tr><td colspan="4"><b>No media yet!</b><br>To send media to your account: email pictures to <a href="mailto:';
    if(TYPE == 'release'){
      $markup .= 'pics@chapresearch.com" target="_top">pics@chapresearch.com</a>.';
    } else if(TYPE == 'test'){
      $markup .= 'test-pics@chapresearch.com" target="_top">test-pics@chapresearch.com</a>.';
    }
      $markup .= ' The subject of the email will become the title and the body of the email will become the description.</td></tr></table>';
    return array('#markup' => $markup);
  }

  // displays all incoming media for a user
  foreach($medias as $media){
    $FID = $media['FID'];
    $file = file_load($FID);
    $uri = $file->uri;
    $variables = array('style_name'=>'preview','path'=>$uri,'width'=>'200','height'=>'200');
    $image = theme_image_style($variables);
    $rawDate = dbDateSQL2PHP($media['dateEntered']);
    $date =  date(SHORT_TIME_FORMAT, $rawDate);
    $title = empty($media['title']) ? '[no title]' : $media['title'];
    $description = wordwrap(chopString($media['description'],30),15,"<br>\n",TRUE);
    $markup .='<tr><td style = "vertical-align: middle;">' . $image . '</td>';
    $markup .='<td style = "vertical-align: middle;"><b>' . $title .'</b><br>' . $date .'<br>' . $description . '</td>';

    // assign media to an outreach
    $markup .='<td style = "vertical-align: middle;"><a href="?q=mediaForm&MID=' . $media['MID'] . '"><button type="button">Assign</button></a></td>';
    // delete incoming media
    $markup .='<td style ="vertical-align: middle;"><a href="?q=removeMedia/'. $media['MID'] .'"><button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></td>'; 
    $markup .='</tr>';    
  }
  $markup .='</table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

//  viewPastUserMedia() - Displays media that the user has already assigned to outreach
function viewPastUserMedia()
{
  global $user;
  $UID = $user->uid;

  // checks to see if the user has a team
  if(dbGetTeamsForUser($user->uid) == false){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // processes the mail messages sent to pics@chapresearch.com
  getIncomingMedia(); 

  $medias = dbGetPastMediaForUser($UID);
  // create header
  $markup = '<h1>Media</h1><br><div class="help tooltip1"><h2>Assigned Media</h2>';

  $markup .= '<span id="helptext"; class="helptext tooltiptext1">';
  $markup .= 'This is past media that you have already assigned to outreach.';
  $markup .= '</span></div>';

  // create table
  $markup .= '<table class="infoTable"><tr><th>Media</th><th>Info</th><th colspan="2">Outreach</th></tr>';

  // if user has not assigned media before
  if(empty($medias)) {
    $markup .= '<tr><td colspan="4">No past media!</td></tr></table>';
    return array('#markup' => $markup);
  }

  // displays previously assigned media
  foreach($medias as $media){
    $FID = $media['FID'];
    $file = file_load($FID);
    $uri = $file->uri;
    $url = generateURL($FID);
    $variables = array('style_name'=>'preview','path'=>$uri,'width'=>'200','height'=>'200');
    $image = theme_image_style($variables);
    $rawDate = dbDateSQL2PHP($media['dateEntered']);
    $date =  date(SHORT_TIME_FORMAT, $rawDate);
    $title = empty($media['title']) ? '[no title]' : $media['title'];
    $description = wordwrap(chopString($media['description'],50),25,"<br>\n",true);
    $markup .='<tr><td style = "vertical-align: middle;"><a href="' .$url .'">' . $image .'</a></td>';
    $markup .='<td style = "vertical-align: middle;"><a href="?q=viewMedia&OID=' . $media['OID'] . '"><b>' . $title .'</b></a>
<br>' . $date .'<br>' . $description . '</td>';
    $markup .='<td style = "vertical-align: middle;">';
    
    if(!empty($name = dbGetOutreachName($media['OID']))) {
      $markup .= '<a href="?q=viewOutreach&OID=' . $media['OID'] . '">' . $name . '</a>';
    } else {
      $markup .= '[none]';
    }

    $markup .= '</td>';
    // delete past media
    $markup .='<td style ="vertical-align: middle;"><a href="?q=removeMedia/' . $media["MID"] . '"><button type="button"><img class="trashIcon" src="/images/icons/trashWhite.png"></button></a></td>';
    $markup .='</tr>';    
  }
  $markup .='</table>';
  return array('#markup' => $markup);
}

// viewMedia() - Displays all media that is linked for an outreach.
function viewMedia()
{
  global $user;

  if(dbGetTeamsForUser($user->uid) == false){
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $params = drupal_get_query_parameters();

  if(!isset($params['OID'])){
    drupal_set_message('No outreach selected!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $OID = $params['OID'];

  $markup = '';
  
  $outreachName = dbGetOutreachName($OID);

  // create header, table
  $markup .= "<table><tr><td><h1>Media for \"$outreachName\" </h1></td>";
  $markup .= '<td style="text-align:right">';
  // upload media button
  $markup .= "<a href=\"?q=uploadMedia&OID=$OID\"><button>Upload Media</button></a>";
  // back to outreach button
  $markup .= "<a href=\"?q=viewOutreach&OID=$OID\"><button>Back to Outreach</button></a></td></tr></table>";

  $media = dbGetMediaForOutreach($OID);
  // create table
  $markup .= '<table class="infoTable"><tr><th>Image</th><th>Name</th><th><Info</th><th>Uploaded By</th><th></th><th></th>';

  // if media for outreach is not empty
  if (!empty($media)){
  
    // displays all media for the outreach
    foreach ($media as $m){
      $url = generateURL($m['FID']);
      $MID = $m['MID'];
      $UIDofMID = dbGetUserForMedia($MID);
      $profile = dbGetUserProfile($UIDofMID);
    
      $markup .= '<tr><td><a href=' . $url .'><img src="' . $url . '" width="200px" height="200px"></a></td>';
      $markup .= '<td>' . $m['title'] .'</td>';
      $markup .= '<td>' . wordwrap(chopString($m['description'],30),15,"<br>\n",TRUE) . '</td>';
      $markup .= "<td>" . $profile['firstName'] . ' ' . $profile['lastName'] . "</td>";
      $markup .= "<td><a href=\"?q=mediaForm&MID=$MID&OID=$OID\"><button><img class=\"editIcon\" src=\"/images/icons/editThumbnailWhite.png\"></button></a></td>";
      // allow user to delete media if he/she was the one to upload it
      if(isMyMedia($MID)){
	$markup .= "<td><a href=\"?q=removeMedia/$MID\"><button><img class=\"trashIcon\" src=\"/images/icons/trashWhite.png\"></button></a></td>";
      } else {
	$markup .= "<td></td>";
      }
    }

    $markup .= '</tr></table>';
  } else {    // if there is no media assigned to the outreach
    $markup .= "<tr>";
    $markup .= '<td style="text-align:center" colspan="10"><em>[None]</em></td>';
    $markup .= "</tr>";
  }
  $markup .= "</table>";


  return array('#markup'=>$markup);
}

?>