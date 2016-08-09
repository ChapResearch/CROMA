<?php

/*
  Used to allow the uploading of media to a specific outreach or to add or delete media for a specific outreach;

  - Contents -
  uploadMedia()- Displays the page to upload media to an outreach using a file on your computer
  uploadMedia_validate()- Validates uploadMedia
  uploadMedia_submit()- Submits uploadMedia
  mediaForm()- Displays the page used to assign incoming media.
  mediaForm_validate()- Validates the mediaForm.
  mediaForm_submit()- Submits the mediaForm
  removeMediaFromForm()- Deletes an image from the form
  removeMediaFromMID()- Deletes an image when passed an $MID
  removeMediaHelper()- a function to help in deleting media. Called by removeMediaFromForm() and removeMediaFromMID().

*/

include_once(MODULES_FOLDER.'/pictures/pictureFunctions.php');

// uploadMedia()- Displays the page to upload media to an outreach using file(s) on your computer

function uploadMedia($form, &$form_state) {
  $form = array();

  $form['fields']['markupOne']=array('#markup'=>'<table><tr><h1>Upload Media</h1></tr><th>Hold [Ctrl] in file selection window to upload multiple files.</th></table>');

  $form['pictures'] = array(
			    '#type' => 'file',
			    '#name' => 'files[]',
			    //			    '#title' => t('Hold [Ctrl] in file selection window to upload multiple files'),
			    '#description' => t('JPG\'s, GIF\'s, and PNG\'s only, 10MB Max Size'),
			    '#attributes' => array('multiple' => 'multiple'),
			    );

  $form['submit'] = array(
			  '#type' => 'submit',
			  '#value' => t('Upload'),
			  //			  '#attributes' => array('onclick' => 'if(!confirm("Are you sure you want to upload this media?")){return false;}'),
			  );

  return $form;
}

// uploadMedia_validate()- Validates uploadMedia

function uploadMedia_validate($form, &$form_state) {
  //Save multiple files
  bulkSavePictures($form_state);
}

//uploadMedia_submit()- Submits uploadMedia

function uploadMedia_submit($form, &$form_state) 
{
  global $user;

  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

  foreach($form_state['values']['file'] as $picture){
    $media = array();
    $media['FID'] = $picture->fid;
    $media['UID'] = $user->uid;
    $media['OID'] = $OID;
    dbAddMedia($media);
    addUsage($picture, 'Media');
  }

  drupal_set_message(t('Upload successful.'));
  drupal_goto('viewMedia', array('query'=>array('OID'=>$OID)));
}

// mediaForm() - Displays the page used to assign incoming media.
//               Note that this form is only used for existing media.
function mediaForm($form, &$form_state) {
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();

  if(isset($params['MID'])){
        $MID = $form_state['MID'] = $params['MID'];    
  }
  else if(isset($form_state['MID'])){
    $MID = $form_state['MID'];
  } else {
    drupal_set_message('No media selected.', 'error');
    return;
  }

  $media = dbGetMedia($MID);
  if (isset($media['OID'])){
    $form_state['OID'] = $media['OID'];
  }

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Assign Media'),
			);
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToMedia'),
				  '#type' => 'submit',				
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );

  $form['fields']['title']=array(
				 '#prefix'=>'<table id="table-fields"><tr><td>',
				 '#type'=>'textfield',
				 '#title'=>t('Media Name'),
				 '#suffix'=>'</td>',
				 '#default_value'=>$media['title']
				 );

  $form['fields']['picture']=array(
				   '#prefix'=>'<td>',
				   '#type'=>'item',
				   '#markup'=> '<img src="' . generateURL($media['FID']) . '" style="max-width:200px; width:auto;  height:auto;">',
				   '#suffix'=>'</td></tr>'
				   );

  $form['fields']['description']=array(
				       '#prefix'=>'<tr><td>',
				       '#type'=>'textarea',
				       '#title'=>t('Description'),
				       '#suffix'=>'</td></tr>',
				       '#default_value'=>$media['description']
				       );

  $team = getCurrentTeam();
  $teams = dbGetTeamsForUser($UID);
  $form_state['teams'] = $teams;
  $TID = $team['TID'];
  $form_state['oldTID'] = $TID;

  if(count($teams) != 1){
    $choices = array();
    
    foreach($teams as $userTeam) {
      $choices[$userTeam['TID']] = $userTeam['number'];
    }

    $form['fields']['team'] = array(
				    '#prefix'=>'<tr><td>',
				    '#type'=>'select',
				    '#title'=>t('Team to be Associated With'),
				    '#default_value' => $TID,
				    '#options'=> $choices,
				    '#chosen'=>true,
				    '#suffix'=>'</td></tr>',
				    '#ajax'=>array(
						   'callback'=>'modify',
						   'limit_validation_errors'=>array(),
						   'wrapper'=>'div_OID_wrapper',
						   ),
				    );
  } else {
    $form['fields']['team'] = array(
				    '#markup' => '<tr><td></td></tr>'
				    );
  }

  $outreachList = dbGetOutreachListForTeam(getCurrentTeam()['TID']); 
  $form_state['outreachList']= $outreachList;

  if(empty($outreachList)){
    drupal_set_message("You don't have any outreaches to assign this to.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  $form['fields']['OID-header'] = array(
				 '#markup'=>'<tr><td>'
					);

  $form['fields']['OID'] = array(
				 '#prefix'=>'<div id="div_OID_wrapper">',
				 '#type'=>'select',
				 '#title'=>t('Outreach Event to be Associated With'),
				 '#default_value'=>$media['OID'],
				 '#validated'=>true,
				 '#options'=>$form_state["outreachList"],
				 '#chosen'=>true,
				 '#suffix'=>'</div>'
				 );

  $form['fields']['OID-footer'] = array(
					'#markup'=>'</td></tr>'
					);

  $form['fields']['remove'] = array(
				    '#prefix'=>'<tr><td>',
				    '#type'=>'submit',
				    '#value'=>t('Delete Picture'),
				    '#limit_validation_errors' => array(),
				    '#submit'=>array("removeMediaFromForm"),
				    '#attributes' => array('onclick' => 'if(!confirm("Are you sure you want to delete this file?")){return false;}'),
				    '#suffix'=>'</td>'
				    );

  $form['fields']['submit'] = array(
				    '#prefix'=>'<td style="text-align:right">',
				    '#type'=>'submit',
				    '#value'=>t('Submit'),
				    '#suffix'=>'</td></tr>'
				    );

  $form['tableFooter']=array('#markup'=>'</table>');

  return $form;  
}
// mediaForm_validate() - Validates the mediaForm.

function mediaForm_validate($form, $form_state) {
  if(empty($form_state['values']['title']))
    form_set_error('title', 'Name cannot be empty.');

  if(empty($form_state['values']['OID']))
    form_set_error('OID', 'Must select outreach event.');

  if(mb_strlen($form_state['values']['description']) > MAX_DESCRIPTION_CHAR)
    form_set_error('description', 'The description must be fewer than '.MAX_DESCRIPTION_CHAR.' characters.');
}

// mediaFrom_submit() - Submits the mediaForm.

function mediaForm_submit($form, $form_state) {
  $names = array('title', 'FID', 'description', 'OID');

  $MID = $form_state['MID'];

  $row = getFields($names, $form_state['values']);
  $row = stripTags($row);
  $TID = dbUpdateMedia($MID,$row);

  if($TID != false) {
    drupal_set_message('Your media has been assigned!');
  } else {
    drupal_set_message('There is a problem. Please try again.');
  }

  drupal_goto('viewMedia', array('query'=>array('OID'=>$row['OID'])));
}

// removeMediaFromForm()- Deletes an image from the form 

function removeMediaFromForm($form=NULL, $form_state=NULL)
{
  removeMediaHelper($form_state['MID']);

  if (isset($form_state['OID'])){
    drupal_goto('viewMedia', array('query'=>array('OID'=>$form_state['OID'])));
  } else {
    drupal_goto('myMedia');
  }
}

// removeMediaFromMID() - Deletes an image when passed an $MID

function removeMediaFromMID($MID, $OID = 0){

  removeMediaHelper($MID);

  if ($OID != 0){
    drupal_goto('viewMedia', array('query'=>array('OID'=>$OID)));
  } else {
    drupal_goto('myMedia');
  }
}

/* removeMediaHelper() - a function to help in deleting media. Called by removeMediaFromForm() and removeMediaFromMID().
 */
function removeMediaHelper($MID){

  $FID = dbDeleteMedia($MID);
  if($FID != null){
    removePicture($FID, 'Media');
  }
}

function backToMedia()
{
  global $user; //getting drupal user info
  $UID = $user->uid; //getting UID from user info
  $params = drupal_get_query_parameters(); //getting the parameters
  $OID = $params['OID'];

  drupal_goto("viewMedia", array('query'=>array('OID'=>$OID)));
}

function modify(&$form, &$form_state) 
{
  $outreachList = dbGetOutreachListForTeam($form_state['values']['team']); 
  $form['fields']['OID']['#options'] = $outreachList;
  $form_state['rebuild'] = TRUE;
  return $form['fields']['OID'];
}


?>