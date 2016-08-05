<?php

/*
  used to allow users to add/edit thumbnails

  - Contents -
  thumbnailForm() - paints form to allow user to edit thumbnail
  cancel() - determines what page to go to after cancel button clicked
*/

function thumbnailForm($form, &$form_state)  
{
  global $user;
  $UID = $user->uid;
  $new = true;
  $params = drupal_get_query_parameters();
  $title = '';

  // if there is an image chosen
  if(isset($params["FID"]) || isset($form_state["URL_FID"])){
    isset($params["FID"])? $FID = $params['FID'] : $FID = $form_state["URL_FID"];
    $form_state['URL_FID'] = $FID;
    $new = false;
  } else {
    drupal_set_message("No Image!", "error");
  }

  // set the title based on which thumbnail is being edited
  if(isset($params["UID"]) || isset($form_state["URL_UID"])){
    isset($params["UID"])? $UID = $params['UID'] : $UID = $form_state["URL_UID"];
    $form_state['URL_UID'] = $UID;
    $title = 'Profile';
  } else if(isset($params["OID"]) || isset($form_state["URL_OID"])){
    isset($params["OID"])? $OID = $params['OID'] : $OID = $form_state["URL_OID"];
    $form_state['URL_OID'] = $OID;
    $title = 'Outreach';
  } else if(isset($params["TID"]) || isset($form_state["URL_TID"])){
    isset($params["TID"])? $TID = $params['TID'] : $TID = $form_state["URL_TID"];
    $form_state['URL_TID'] = $TID;
    $title = 'Team';
  } else {
    drupal_set_message("Nothing to edit!", "error");
  }
  
  $form = array();
  
  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Edit: ' . $title .' Thumbnail'),
			);

  $form['fields']['tableHeader']=array(
                                       '#markup'=>'<table>'
                                       );

  if(!$new){
    $form['fields']['cancelChanges']=array(
					   '#prefix'=>'<left>',
					   '#limit_validation_errors' => array(),
					   '#submit'=>array('cancel'),
					   '#type' => 'submit',
					   '#value' => '⇦ Cancel Changes',
					   '#attributes' => array(
								  'OnSubmit' =>'if(!confirm("Cancel?")){return false;}'),
					   '#suffix'=>'</left>'
					   );
  }

  if ($new){
    $oldFID = '';
  } else {
    $oldFID = $FID;
    $form_state['oldFID'] = $oldFID;
    if(isset($form_state['values']['FID'])){
      $newFID = $form_state['values']['FID'];
    } else {
      $newFID = $oldFID;
    }
  }

  $form['fields']['preTabling']=array('#markup'=>'<tr><td colspan="6" style="text-align:center">');

  $form['fields']['FID'] = generatePictureField('Picture', $oldFID);

  $form['fields']['sufTabling']=array('#markup'=>'</td></tr><tr>');

  $form['fields']['submit']=array(
				  '#prefix'=>'<td colspan="3" style="text-align:right">',
				  '#type'=>'submit',
				  '#value'=>t('Save'),
				  '#suffix'=>'</td>'
				  );


  $form['footer']=array('#markup'=>'</tr></table>');

  return $form;
}

function thumbnailForm_validate($form, $form_state)
{
}


function thumbnailForm_submit($form, $form_state)
{
  $params = drupal_get_query_parameters();

  // getting the inputted info from the fields
  $fields = array("FID");
  $picData = getFields($fields, $form_state['values']);
  $picData = stripTags($picData, '');

  $oldFID = isset($form_state['oldFID'])?$form_state['oldFID']:0;

  if (isset($params["UID"])){   // if updating user's profile picture
    $UID = $params["UID"];
    replacePicture($picData['FID'], $oldFID, 'Users');
    dbUpdate("profiles",$picData,"UID", $UID);
    drupal_goto("viewUser", array('query'=>array('UID'=>$UID)));
  } else if (isset($params["OID"])){    // if editing outreach thumbnail
    $OID = $params["OID"];
    replacePicture($picData['FID'], $oldFID, 'Outreach');
    dbUpdateOutreach($OID, $picData);
    drupal_goto("viewOutreach", array('query'=>array('OID'=>$OID)));
  } else if (isset($params["TID"])){ // if editing team thumbnail
    $TID = $params["TID"];
    replacePicture($picData['FID'], $oldFID, 'Teams');
    dbUpdateTeam($TID,$picData);
    drupal_goto("viewTeam", array('query'=>array('TID'=>$TID)));
  } else {
    drupal_goto("myDashboard");
  }
}

function cancel()
{
  $params = drupal_get_query_parameters();

  // sets the page to return to based on which params are set
  if (isset($params["UID"])){
    $retPage = 'viewUser';
  } else if(isset($params["OID"])){
    $retPage = 'viewOutreach';
  } else if(isset($params["TID"])){
    $retPage = 'viewTeam';
  } else{
    $retPage = 'myDashboard';
  }

  drupal_goto($retPage, array('query'=>$params));
}

?>