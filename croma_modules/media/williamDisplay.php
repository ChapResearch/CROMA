<?php

/*

---- media/williamDisplay.php ----

Used for display and assigning of media items.

- Contents -
viewIncomingMedia() - Displays information for all media associated with the user.
assignMedia() - Displays the page used to assign incoming media.

*/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/croma_modules/helperFunctions.inc");
include_once("/var/www-croma/database/croma_dbFunctions.php");

// viewIncomingMedia() - Displays information for all media associated with the user.

function viewIncomingMedia() 
{ 
  global $user;
  $UID = $user->uid;

  getIncomingMedia();

  $medias = dbGetIncomingMediaForUser($UID);
  $markup = '';  
  if(empty($medias)){
    $markup .= 'No media yet!';
  } else { 
    $markup .='<div align="right"><button type="button">All Media</button></div>';  
    $markup .='<table>';
    $date = date('Y-m-d');

    foreach($medias as $media){
      $picture = file_load($media['FID']);
      $url = file_create_url($picture->uri);
      $markup .='<tr><td style = "vertical-align: middle;"><img src="' . $url . '" height="50" width="50"></td>';
      $markup .='<td style = "vertical-align: middle;">' . $media["title"] .'<br>' . $date .'<br>' . $media["description"] . '</td>';
      $markup .='<td style = "vertical-align: middle;"><a href="http://croma.chapresearch.com/?q=assignMedia&MID=' . $media['MID'] . '"><button type="button">Assign</button></a></td>';
      $markup .='</tr>';    
    }
  
    $markup .='</table>';
  }

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

// assignMedia() - Displays the page used to assign incoming media.

function assignMedia() {
  global $user;
  $UID = $user->uid;

  $params = drupal_get_query_parameters();
  $media = dbGetIncomingMediaForUser($UID);
  $MID = $params['MID'];

  // TODO - use DB call
  foreach($media as $medium) {
    if($medium['MID'] == $MID) {
      $FID = $medium['FID'];
      $dateEntered = $medium['dateEntered'];
      break;
    }
  }

  $pic = file_load($FID);
  $markup = '<img src="' . file_create_url($pic->uri) . '" style="width:200px; height:200px;"><br>';
  $markup .= '<b>Date: </b>' . $dateEntered . '<br>';
  $params = drupal_get_query_parameters();

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Assign Media'),
			);

  $form['fields']['title']=array(
				 '#prefix'=>'<table><tr><td>',
				 '#type'=>'textfield',
				 '#title'=>t('Media Name'),
				 '#suffix'=>'</td></tr>',
				 '#default_value'=>$medium['title']
				 );

  $form['fields']['FID']=array(
				 '#prefix'=>'<tr style="display:none"><td>',
				 '#type'=>'textfield',
				 '#title'=>t('FID'),
				 '#default_value'=>$FID,
				 '#suffix'=>'</td></tr>'
				 );

  $form['fields']['description']=array(
				       '#prefix'=>'<tr><td>',
				       '#type'=>'textarea',
				       '#title'=>t('Description'),
				       '#suffix'=>'</td></tr>',
				       '#default_value'=>$medium['description']
				       );

  // TODO - comment

  $OIDSelect = array();
  $teams = dbGetTeamsForUser($UID);
  $outreaches = dbGetOutreachesForTeam($teams[0]['TID']); // TODO - fix which team

  foreach($outreaches as $outreach) {
    $OIDSelect[$outreach['OID']] = $outreach['name'];
  }

  $form['fields']['OID'] = array(
				 '#prefix'=>'<tr><td>',
				 '#type'=>'select',
				 '#title'=>t('Outreach Event to be Associated With'),
				 '#default_value'=>'',
				 '#options'=>$OIDSelect,
				 '#suffix'=>'</td></tr>'
				 );

  $form['fields']['footer'] = array(
				    '#markup'=>'</table>'
				    );

  $form['fields']['submit'] = array(
				    '#type'=>'submit',
				    '#value'=>t('Submit')
				    );
  
  return $form;  
}
// assignMedia_validate() - Validates the assignMedia.

function assignMedia_validate($form, $form_state) {
  if(empty($form_state['values']['title']))
    form_set_error('title', 'Name cannot be empty');

  if(empty($form_state['values']['OID']))
    form_set_error('OID', 'Must select outreach event');
}

// assignMedia_submit() - Submits the assignMedia.

function assignMedia_submit($form, $form_state) {
  $names = array('name', 'FID', 'description', 'OID');

  $params = drupal_get_query_parameters();
  $MID = $params['MID'];

  $row = getFields($names, $form_state['values']);
  $TID = dbUpdateMedia($MID,$row);

  if($TID != false) {
    drupal_set_message('Your media has been assigned!');
  } else {
    drupal_set_message('There is a problem. Please try again.');
  }

  drupal_goto('viewMedia', array('query'=>array('OID'=>$row['OID'])));
}
  
?>