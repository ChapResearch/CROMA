<?php

/*
  Used to allow users to create/edit outreach

  - Contents -
  outreachForm() - creates the form for outreach data (including times)
  deleteProfile() - used as a menu hook to "delete" (aka disable) the user
  changeCancel() - used as a menu hook to toggl whether the outreach event is cancelled
  duplicateOutreach() - used to make a copy of the outreach event
*/   

function addDateRow($form, &$form_state) {
  $form_state['numRows']++;
  $form_state['rebuild'] = TRUE;
}

function removeDateRow($form, &$form_state) {
  $form_state['numRows']--;
  $form_state['rebuild'] = TRUE;
}

function modifyDateRows_callback($form, $form_state) {
  return $form['fields']['dates'];
}

function outreachForm($form, &$form_state)
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $form_state['TID'] = $TID;
  $new = $form_state['new'] = true;


  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if(isset($params["OID"])){
    $form_state['OID'] = $params['OID'];
    $new = $form_state['new'] = false;
    $outreach = dbGetOutreach($params["OID"]);
    $times = dbGetTimesForOutreach($params["OID"]);
    $form_state['numRows'] = count($times); // set to display all times
    $form_state['initialNumTimes'] = count($times); // record how many were there before (in case user deletes one)
    $TID = $outreach['TID'];
    $form_state['TID'] = $TID;
  }

  $form = array();

  if(!$new){

  //Menu Hook begins
      $confirmBoxJS = "if(!confirm('Are you sure you want to duplicate this outreach? Any changes you made just now will NOT be saved.')){return false;}";
      
      
      $form['duplicate']=array(
			       '#prefix'=>"<div style=\"text-align:right\"><a href=\"?q=duplicateOutreach/{$form_state['OID']}/$TID\">",
			       '#markup'=>"<button onclick=\"$confirmBoxJS\" type=\"button\">Duplicate</button>",
			       '#suffix'=>'</a>'
			       );
  }


  if (!$new){

  //Menu Hook

    if($outreach["cancelled"]){

 $confirmBoxJS = "if(!confirm('Are you sure you want to uncancel this outreach for the team?')){return false;}";
      
      $form['cancel']=array(
			    '#prefix'=>"<a href=\"?q=cancelOutreach/{$form_state['OID']}/$TID\">",
			    '#markup'=>"<button onclick=\"$confirmBoxJS\" type=\"button\">Uncancel Outreach</button>",
			    '#suffix'=>'</a></div>'
			    );     

    } else {

      $confirmBoxJS = "if(!confirm('Are you sure you want to cancel this outreach for the team? This will remove the outreach from current team use.')){return false;}";
      
      $form['cancel']=array(
			    '#prefix'=>"<a href=\"?q=cancelOutreach/{$form_state['OID']}/$TID\">",
			    '#markup'=>"<button onclick=\"$confirmBoxJS\" type=\"button\">Cancel From Team Use</button>",
			    '#suffix'=>'</a></div>'
			    );
    }
  }  

  $teamNumb = dbGetTeamNumber($TID);

  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Add Outreach: Team ' . '<b>' . $teamNumb . '</b>'),
			);

  if(!$new){
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToEvent'),
				  '#type' => 'submit',
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }

  $form['fields']['markupOne']=array('#markup'=>'<table>');

  $form['fields']['name']=array(
				'#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				'#type'=>'textfield',
				'#title'=>t('Outreach Name:'),
				'#default_value'=>$new?'':$outreach['name'],
				'#placeholder'=>'Name of the event',
				'#attributes'=>array('onsubmit'=>'return false'),
				'#suffix'=>'</td>'
				);

  $tags = dbGetOutreachTagsForTeam($TID);

  if(empty($tags)){
    if(hasPermissionForTeam('manageOutreachTags', $TID)){
      $msg = 'Click <a href="?q=teamOutreachSettings"><b>here</b></a> to change your settings.';
    } else {
      $msg = 'Please have a team admin or owner set up tags for your team.';
    }
    drupal_set_message('To give the outreach a tag, you need to set up the tag options for your team! '.$msg, 'error');

    $form['fields']['tags']=array(
				  '#prefix'=>'<td colspan="3" style="text-align:center">',
				  '#markup'=>'Tags:<br><em>You have no available tags!</em>',
				  '#suffix'=>'</td></tr>'
				  );
  } else {

    if(!$new){
      $oldTags = dbGetTagsForOutreach($form_state['OID'], true); // get only OTID's to satisfy select field
    }

    $form['fields']['tags']=array(
				  '#prefix'=>'<td colspan="3" style="text-align:center">',
				  '#type'=>'select',
				  '#id'=>'tags_field',
				  '#title'=>t('Tags:'),
				  '#options'=>$tags,
				  '#default_value'=>$new?'':$oldTags,
				  '#chosen'=>true,
				  '#multiple'=>true,
				  '#suffix'=>'</td></tr>'
				  );
  }

  if(hasPermissionForTeam('manageOutreachTags', $TID)){
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#default_value'=>$new?'Select':$outreach['status'],
				    '#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
				    '#title'=>t('Status:'),
				    '#chosen'=>true,
				    '#suffix'=>'</td>'
				    );
  } else if(!$new && $outreach['status'] == 'isIdea'){
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#title'=>t('Status:'),
				    '#options'=> array('isIdea'=>'Idea'),
				    '#default_value'=>'isIdea',
				    '#disabled'=>true,
				    '#suffix'=>'</td>'
				    );

  } else if(!$new && $outreach['status'] == 'isOutreach'){
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#title'=>t('Status:'),
				    '#options'=> array('isOutreach'=>'Outreach'),
				    '#default_value'=>'isOutreach',
				    '#disabled'=>true,
				    '#suffix'=>'</td>'
				    );
	
  } else if(!$new && $outreach['status'] == 'doingWriteUp'){
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#title'=>t('Status:'),
				    '#options'=> array('doingWriteUp'=>'Write-Up'),
				    '#default_value'=>'doingWriteUp',
				    '#disabled'=>true,
				    '#suffix'=>'</td>'
				    );

  } else if(!$new && $outreach['status'] == 'locked'){
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#title'=>t('Status:'),
				    '#options'=>array('locked'=>'Locked'),
				    '#default_value'=>'locked',
				    '#disabled'=>true,
				    '#suffix'=>'</td>'
				    );
	
  } else {
	  
    $form['fields']['status']=array(
				    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				    '#type'=>'select',
				    '#default_value'=>$new?'Select':$outreach['status'],
				    '#options'=> array('isIdea'=>'Idea'),
				    '#title'=>t('Status:'),
				    '#chosen'=>true,
				    '#suffix'=>'</td>'
				    );
	
  }
  
  /*  $form['fields']['status']=array(
    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
    '#type'=>'select',
    '#default_value'=>$new?'Select':$outreach['status'],
				  '#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
				  '#title'=>t('Status:'),
				  '#default_value'=>$new?'':$outreach['status'],
				  '#chosen'=>true,
				  '#suffix'=>'</td>'
				  );
  */

  $form['fields']['manageTagsBttn']=array(
					  '#prefix'=>'<td colspan ="3" style="text-align:center">',
					  '#markup'=>'<a href="?q=teamOutreachSettings" target="_blank"><button type="button">Manage Tags</button></a>',
					  '#suffix'=>'</td></tr>'
					  );


  $form['fields']['description']=array(
				       '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
				       '#type'=>'textarea',
				       '#title'=>t('Description:'),
				       '#default_value'=>$new?'':$outreach['description'],
				       '#placeholder'=>'Maximum of 500 characters',
				       '#suffix'=>'</td></tr>',
				       '#maxlength_js'=>'TRUE',
				       '#maxlength'=>'500'
				       );

  $form['fields']['address']=array(
				   '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				   '#type'=>'textfield',
				   '#title'=>t('Address:'),
				   '#default_value'=>$new?'':$outreach['address'],
				   '#suffix'=>'</td>'
				   );

  $team = dbGetTeam($TID);

  $form['fields']['city']=array(
				'#prefix'=>'<td colspan="1" style="text-align:center">',
				'#type'=>'textfield',
				'#title'=>t('City:'),
				'#default_value'=>$new?$team['city']:$outreach['city'],
				'#suffix'=>'</td></tr>'
				);

  $form['fields']['state']=array(
				 '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				 '#type'=>'select',
				 '#title'=>t('State:'),
				 '#options'=>states_list(),
				 '#default_value'=>$new?$team['state']:$outreach['state'],
				 '#chosen'=>true,
				 '#suffix'=>'</td>'
				 );

  $form['fields']['country']=array(
				   '#prefix'=>'<td colspan="3" style="text-align:center">',
				   '#type'=>'select',
				   '#title'=>t('Country:'),
				   '#options'=>countries_list(),
				   '#default_value'=>$new?$team['country']:$outreach['country'],
				   '#chosen'=>true,
				   '#suffix'=>'</td></tr>'
				   );

    
  $form['fields']['co_firstName']=array(
					'#prefix'=>'<tr><td colspan="3" style="text-align:center">',
					'#type'=>'textfield',
					'#title'=>t("Host Contact's First Name:"),
					'#default_value'=>$new?'':$outreach['co_firstName'],
					'#placeholder'=>'First name of contact for this outreach',
					'#suffix'=>'</td>'
					);

  $form['fields']['co_lastName']=array(
				       '#prefix'=>'<td colspan="3" style="text-align:center">',
				       '#type'=>'textfield',
				       '#title'=>t("Host Contact's Last Name:"),
				       '#default_value'=>$new?'':$outreach['co_lastName'],
				       '#placeholder'=>'Last name of contact for this outreach',
				       '#suffix'=>'</tr>'
				       );


  $form['fields']['co_phoneNumber']=array(
					  '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
					  '#type'=>'textfield',
					  '#title'=>t("Host Contact's Phone Number:"),
					  '#default_value'=>$new?'':$outreach['co_phoneNumber'],
					  '#placeholder'=>'Format: XXXXXXXXXX',
					  '#suffix'=>'</td>'
					  );

  $form['fields']['co_email']=array(
				    '#prefix'=>'<td colspan="3" style="text-align:center">',
				    '#type'=>'textfield',
				    '#title'=>t("Host Contact's Email:"),
				    '#default_value'=>$new?'':$outreach['co_email'],
				    '#placeholder'=>'Email of contact for this outreach',
				    '#suffix'=>'</td></tr>'
				    );


  $form['fields']['co_organization']=array(
					   '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
					   '#type'=>'textfield',
					   '#title'=>t("Host Organization:"),
					   '#default_value'=>$new?'':$outreach['co_organization'],
					   '#placeholder'=>'Name of group participating with for this outreach',
					   '#suffix'=>'</td></tr>'
					   ); 


  //Moved to writeUp Form

  /*  $form['fields']['totalAttendance']=array(
					   '#prefix'=>'<td colspan="3" style="text-align:center">',
					   '#type'=>'textfield',
					   '#title'=>t('Total Event Attendance:'),
					   '#default_value'=>$new?NULL:$outreach['totalAttendance'],
					   '#placeholder'=>'Total people that attended the event (i.e. 2000)',
					   '#states' => array(
							      'invisible' => array(
										   ':input[name="status"]' => array('value' => 'isIdea'),
										   ),
							      ),
					   '#suffix'=>'</td></tr>'
					   );
  */

  //Picture moved to editThumbnail

  /*  $form['fields']['thumbnail']=array(
				     '#markup'=>'<td colspan="3" style="text-align:center">'
				     );
  if ($new){
    $oldFID = '';
  } else {
    $oldFID = $outreach['FID'];
    $form_state['oldFID'] = $oldFID;
  }

  $form['fields']['FID'] = generatePictureField('Thumbnail Picture for Outreach', $oldFID);
     
  $form['fields']['footer'] = array('#markup'=>'</td></tr>');

  */

  if (empty($form_state['numRows'])) {
    $form_state['numRows'] = 1;
  }
  
    $form['fields']['datesHeader']=array(
					 '#markup'=>'<tr><td colspan="6">'
					 );

    $form['fields']['dates']['header']=array('#markup'=>'<div id="dates-div"><table>');

    $date = date(DEFAULT_TIME_FORMAT, strtotime('today noon'));

    for($i = 0; $i < $form_state['numRows']; $i++){

      $form['fields']['dates']["openFieldOfStart-$i"] = array(
							      '#markup'=>'<tr><td colspan="2" align="left" style="text-align:left">'
							      );
      if(!empty($times)){
	$startTime = date(DEFAULT_TIME_FORMAT, dbDateSQL2PHP($times[$i]['startTime']));    
      }
      $form['fields']['dates']["startTime-$i"] = array(
						       '#type' => 'date_popup', 
						       '#title' => t('Start Date:'),
						       '#default_value' => !isset($startTime)?$date:$startTime,
						       '#date_format' => TIME_FORMAT,
						       '#date_label_position' => 'within', 
						       '#date_increment' => 1,
						       '#date_year_range' => '-20:+20',
						       '#datepicker_options' => array(),
						       '#states' => array(
									  'invisible' => array(
											       ':input[name="status"]' => array('value' => 'isIdea'),
											       ),
									  ),
						       );

      if(!$new){
	$form_state['fields']['dates']["TOID-$i"] = $times[$i]['TOID'];
      }
    
      $form['fields']['dates']["closeFieldOfStart-$i"] = array('#markup'=>'</td>');



      $form['fields']['dates']["openFieldOfEnd-$i"] = array('#markup'=>'<td colspan="2" align="right" style="text-align:left">');

      if(!empty($times)){
	$endTime = date(DEFAULT_TIME_FORMAT, dbDateSQL2PHP($times[$i]['endTime']));
      }
      $form['fields']['dates']["endTime-$i"] = array(
						     '#type' => 'date_popup',
						     '#title' => t('End Date:'),
						     '#default_value' => !isset($endTime)?$date:$endTime,
						     '#date_format' => TIME_FORMAT,
						     '#date_label_position' => 'within',
						     '#date_increment' => 1,
						     '#date_year_range' => '-20:+20', 
						     '#datepicker_options' => array(),
						     '#states' => array(
									'invisible' => array(
											     ':input[name="status"]' => array('value' => 'isIdea'),
											     ),
									),
						     );

      $form['fields']['dates']["closeFieldOfEnd-$i"] = array('#markup'=>'</td>');
					 
      if($i == $form_state['numRows'] - 1){
	$form['fields']['dates']["addRowButton-$i"]=array(
							  '#prefix'=>'<td colspan="1" style="text-align:center">',
							  '#type'=>'submit',
							  '#submit'=>array('addDateRow'),
							  '#value'=>'+',
							  '#limit_validation_errors' => array(),
							  '#ajax'=>array(
									 'callback'=>'modifyDateRows_callback',
									 'wrapper'=>'dates-div'
									 ),
							  '#states' => array(
									     'invisible' => array(
												  ':input[name="status"]' => array('value' => 'isIdea'),
												  ),
									     ),
							  '#suffix'=>'</td>'
							  );
      }
      if($i == $form_state['numRows'] - 1){
	$form['fields']['dates']["removeRowButton-$i"]=array(
							     '#prefix'=>'<td colspan="1" style="text-align:center">',
							     '#type'=>'submit',
							     '#submit'=>array('removeDateRow'),
							     '#value'=>'-',
							     '#limit_validation_errors' => array(),
							     '#ajax'=>array(
									    'callback'=>'modifyDateRows_callback',
									    'wrapper'=>'dates-div'
									    ),
							     '#states' => array(
										'invisible' => array(
												     ':input[name="status"]' => array('value' => 'isIdea'),
												     ),
										),
							     '#suffix'=>'</td>'
							     );
      }
      $form['fields']['dates']["rowFooter-$i"]=array('#markup'=>'</tr>');
    } // end of for loop

    $form['fields']['dates']["divFooter-$i"]=array('#markup'=>'</table></div></td>');


    //Moved to writeUp Form

    /*

  $form['fields']['testimonial']=array(
				       '#prefix'=>'<tr><td colspan="6" style="text-align:center">',
				       '#type'=>'textarea',
				       '#title'=>t('Comments/Testimonials:'),
				       '#default_value'=>$new?'':$outreach['testimonial'],
				       '#placeholder'=>'Maximum of 500 characters',
				       '#states' => array(
							    'invisible' => array(
										 ':input[name="status"]' => array('value' => 'isIdea'),
										 ),
							  ),
				       '#suffix'=>'</td></tr>',
				       '#maxlength_js'=>'TRUE',
				       '#maxlength'=>'500'
				       );

    */

  if (!$new && $outreach["status"] == "locked"){

      $isPublicOptions = array(0 => t('Private'), 1 => t('Public'));

      $form['fields']['isPublic']=array(
					'#prefix'=>'<tr><td colspan="3" style="text-align:center">',
					'#type'=>'radios',
					'#title'=>t('Set Event Visibility'),
					'#options' => $isPublicOptions,
					'#default_value'=>$new?'1':$outreach['isPublic'],
					'#suffix'=>'</td></tr>'
					);

    }

  //  $form['fields']['html1']=array('#markup'=>'</td></tr><tr><td>');

  $form['fields']['submit']=array(
			'#prefix'=>'<tr><td colspan="6" style="text-align:right">',
			'#type' => 'submit',
			'#value'=>t('Save'),
			'#sorted'=>false,
			'#suffix'=>'</td></tr>'
			);


  $form['fields']['finalFooter']=array('#markup'=>'</table>');

  if(!$new && !(hasPermissionForTeam('editAnyOutreach', $TID) || isMyOutreach($params['OID']))){
    drupal_set_message("You don't have permission to edit this outreach!", 'error');
    drupal_goto('viewOutreach', array('query'=>array('OID'=>$params['OID'])));
  }

  if(dbGetTeamsForUser($user->uid) == NULL) {
    drupal_set_message("You don't have a team assigned!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  if(!(dbIsUserApprovedForTeam($UID, $TID))){
    drupal_set_message("You aren't approved for this team.", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
    
  if(dbGetStatusForTeam($TID) == "0" || dbGetStatusForTeam($TID) == FALSE){
    drupal_set_message("This team isn't active/approved!", 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
    
  return $form;

}

function outreachForm_validate($form, $form_state)
{
  if(empty($form_state['values']['name'])){
    form_set_error('name','Name cannot be empty.');
  }

  /*  if(!empty($form_state['values']['totalAttendance']) &&
     !is_numeric($form_state['values']['totalAttendance'])){
    form_set_error('totalAttendance','Total attendance must be a number.');
    }*/

  if(!empty($form_state['values']['description'])) {
    if(strlen($form_state['values']['description'])>999){
      form_set_error('description',"The description must be fewer than 500 characters.");
    }
  }

  /*
  if(!empty($form_state['values']['testimonial'])) {
    if(strlen($form_state['values']['testimonial'])>999){
      form_set_error('testimonial',"The testimonial must be fewer than 500 characters.");
    }
    }*/

  if(!empty($form_state['values']['name'])) {
    if(strlen($form_state['values']['name'])>50){
      form_set_error('name',"The name must be fewer than 50 characters.");
    }
  }

  if(!empty($form_state['values']['co_phoneNumber'])) {
    if(strlen($form_state['values']['co_phoneNumber'])>20){
      form_set_error('co_phoneNumber',"The phone number of your host must be fewer than 20 characters.");
    } else if(!is_numeric($form_state['values']['co_phoneNumber'])){
      form_set_error('co_phoneNumber','Phone numbers must be numeric!'); // checks that phone number is all numbers
    }
  }

  if(!empty($form_state['values']['co_firstName'])) {
    if(strlen($form_state['values']['co_lastName'])>30){
      form_set_error('co_firstName',"The first name of your host must be fewer than 30 characters.");
    }
  }

  if(!empty($form_state['values']['co_lastName'])) {
    if(strlen($form_state['values']['co_lastName'])>30){
      form_set_error('co_lastName',"The last name of your host must be fewer than 30 characters.");
    }
  }

  if(!empty($form_state['values']['co_email'])) {
    if(strlen($form_state['values']['co_email'])>30){
      form_set_error('co_email',"The email of your host must be fewer than 30 characters.");
    }
  }

  if(!empty($form_state['values']['address'])) {
    if(strlen($form_state['values']['address'])>30){
      form_set_error('address',"The address must be fewer than 30 characters.");
    }
  }

  if(!empty($form_state['values']['city'])) {
    if(strlen($form_state['values']['city'])>30){
      form_set_error('city',"The city must be fewer than 30 characters.");
    }
  }

  /*  if(!empty($form_state['values']['totalAttendance'])) {
    if(strlen($form_state['values']['totalAttendance'])>11){
      form_set_error('totalAttendance',"The total attendance must be fewer than 11 numbers long.");
    }
    }*/

  if(!empty($form_state['values']['peopleImpacted'])) {
    if(strlen($form_state['values']['peopleImpacted'])>11){
      form_set_error('peopleImpacted',"The people impacted must be fewer than 11 numbers long.");
    }
  }

  for($i = 0; $i < $form_state['numRows']; $i++){
    // check if start/end times are backward
    if($form_state['values']["endTime-$i"] < $form_state['values']["startTime-$i"]){
      form_set_error("endTime-$i", "The end time must be after the start time.");
    }
    // check that the time range doesn't overlap any others
    for($x = 0; $x < $form_state['numRows']; $x++){
      if($x != $i){ // if not checking the current time period
	if($form_state['values']["startTime-$i"] > $form_state['values']["startTime-$x"] &&
	   $form_state['values']["startTime-$i"] < $form_state['values']["endTime-$x"]){
	  form_set_error("startTime-$i", "The time ranges cannot overlap.");
	}
      }
    } // done checking overlap (inner loop)
  } // done checking times (outer loop)
}

function outreachForm_submit($form, $form_state)
{
  global $user;
  $UID = $user->uid;
  $TID = $form_state['TID'];

  $outreachFields = array("name", "peopleImpacted", "address", "city", "state", "country", "status", "co_organization", "co_firstName", "co_lastName", "co_email", "co_phoneNumber", "isPublic");
  $outreachData = getFields($outreachFields, $form_state['values']);
  $outreachData = stripTags($outreachData, ''); // remove all tags
  $outreachData['description'] = stripTags(array($form_state['values']['description'])); // allow some tags
  //  $outreachData['testimonial'] = stripTags(array($form_state['values']['testimonial'])); 
  $outreachData["TID"] = $TID;
  if($form_state['new']){
    $outreachData["UID"] = $UID;
  }

  if (isset($form_state['OID'])){ 
    $OID = $form_state['OID'];
    $oldOutreachData = dbGetOutreach($OID);
    if($outreachData["status"] == "doingWriteUp" && $oldOutreachData["isWriteUpApproved"] == true){
      $outreachData["writeUpUID"] = null;
      $outreachData["isWriteUpSubmitted"] = 0;
      $outreachData["isWriteUpApproved"] = 0;
    }
  }

  /*  if($outreachData['totalAttendance'] == ''){
e    $outreachData['totalAttendance'] = NULL;
    }*/

  //  $oldFID = isset($form_state['oldFID'])?$form_state['oldFID']:0;
  //  replacePicture($outreachData['FID'], $oldFID, 'Outreach'); // does its own checks

  if (!$form_state['new']){ // updating existing event
    $OID = $form_state['OID'];
    $result = dbUpdateOutreach($OID, $outreachData);

    if ($result){ // if db call was successful
      for($i = 0; $i < $form_state['numRows']; $i++){ // loop through date rows
	$TOID = isset($form_state['fields']['dates']["TOID-$i"])?$form_state['fields']['dates']["TOID-$i"]:0;
	$timeData['startTime'] = dbDatePHP2SQL(strtotime($form_state['values']["startTime-$i"]));
	$timeData['endTime'] = dbDatePHP2SQL(strtotime($form_state['values']["endTime-$i"]));
	if ($timeData['startTime'] != null && $timeData['endTime'] != null){ // if row isn't empty
	  if($TOID != 0){ // update existing record
	    dbUpdateTimesForOutreach($TOID, $timeData);
	  } else { // add a new time record if there wasn't one previously
	    $timeData['OID'] = $OID;
	    dbAddTimesToOutreach($timeData);
	  }
	} else { // remove time record if empty
	  dbRemoveTimeFromOutreach($TOID);
	}
      }
      for($i = $form_state['numRows']; $i < $form_state['initialNumTimes']; $i++){ // executes if times were deleted
	dbRemoveTimeFromOutreach($form_state['fields']['dates']["TOID-$i"]);
      }

      $notification = array();
      $userName = dbGetUserName($user->uid);
      $outName = dbGetOutreachName($OID);
      $notification['dateCreated'] = dbDatePHP2SQL(time());
      $notification['dateTargeted'] = dbDatePHP2SQL(time());
      $notification['message'] = "$userName has updated outreach $outName.";
      $notification['bttnTitle'] = 'View';
      $notification['bttnLink'] = '?q=viewOutreach&OID=' . $OID;
      $notification['TID'] = $TID;
      notifyUsersByRole($notification, 'moderator');

      // handle tags
      if(!empty($form_state['values']['tags'])){      
	$newTags = $form_state['values']['tags'];
	$previous = dbGetTagsForOutreach($OID, true); // the "true" means this will return only OTID's
	if($previous == false){ // if there aren't any tags
	  $previous = array();
	}
	$deleted = array_diff($previous, $newTags);
	$added = array_diff($newTags, $previous);
	foreach($deleted as $delete){ // $delete is the OTID to be removed from the outreach
	  if(!empty($delete)){
	    dbRemoveTagFromOutreach($delete, $OID); 
	  }
	}
	foreach($added as $add){ // $add is the OTID to be added to the outreach
	  if(!empty($add)){
	    dbAddTagToOutreach($add, $OID); 
	  }
	}
      }

      drupal_set_message("Outreach updated!");
    } else {
      drupal_set_message("Outreach not updated!");
    }
  } else { // adding new event
    $outreachData['logDate'] = dbDatePHP2SQL(time());
    $OID = dbCreateOutreach($outreachData);
    if ($OID != false){
      dbAddUserAsOwnerOfOutreach($UID, $OID);
      dbAssignUserToOutreach($UID, $OID, 'owner');

      // handle times
      if($outreachData['status'] != 'isIdea') {
	for($i = 0; $i < $form_state['numRows']; $i++){ 
	  $time = array("startTime-$i", "endTime-$i");
	  $timeData = getFields($time, $form_state['values']);
	  if ($timeData["startTime-$i"] != null && $timeData["endTime-$i"] != null){
	    // rename array keys to match columns
	    $timeData['startTime'] = dbDatePHP2SQL(strtotime($timeData["startTime-$i"]));
	    $timeData['endTime'] = dbDatePHP2SQL(strtotime($timeData["endTime-$i"]));
	    unset($timeData["endTime-$i"], $timeData["startTime-$i"]);
	    $timeData['OID'] = $OID;
	    dbAddTimesToOutreach($timeData);
	  }
	}
      }

      // handle tags
      if(!empty($form_state['values']['tags'])){
	foreach($form_state['values']['tags'] as $OTID){
	  dbAddTagToOutreach($OTID, $OID);
	}
      }

      // create notification
      $notification = array();
      $userName = dbGetUserName($user->uid);
      $outName = dbGetOutreachName($OID);
      $notification['dateCreated'] = dbDatePHP2SQL(time());
      $notification['dateTargeted'] = dbDatePHP2SQL(time());
      $notification['message'] = "$userName has created outreach $outName.";
      $notification['bttnTitle'] = 'View';
      $notification['bttnLink'] = '?q=viewOutreach&OID=' . $OID;
      $notification['TID'] = $TID;
      notifyUsersByRole($notification, 'moderator');
      drupal_set_message("Outreach created!");

    } else { // if the $OID IS false
      form_set_error("Outreach not created successfully");
    }
  }
  if(dbIsOutreachOver($OID)){
    drupal_set_message("It appears you are logging an old event. Don't forget to <a href=\"?q=logHours&OID=$OID\"><b>log old hours</b></a>!");
  }
  /*  if (isset($params['url'])){
    drupal_goto($params['url'], array('query'=>$params));
    }*/
    drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
}

function changeCancel($OID)
{
  $outreach = dbGetOutreach($OID);

  if($outreach['cancelled']) {
    dbUncancelEvent($OID);
    drupal_set_message('Outreach uncancelled!');
  } else {
    dbCancelEvent($OID);
    drupal_set_message('Outreach cancelled!');
  }

  if(isset($_SERVER['HTTP_REFERER'])){
    drupal_goto($_SERVER['HTTP_REFERER']);
  } else {
    drupal_goto('viewOutreach', array('query'=>array('OID'=>$OID)));
  }
}

function duplicateOutreach($OID)
{
  $newOID = dbDuplicateOutreach($OID);
  if($newOID != false){
    $worked = dbDuplicateOutreachTimes($OID, $newOID);
    if($worked){
      drupal_set_message('Outreach duplicated!');
      if(isset($_SERVER['HTTP_REFERER'])){
	drupal_goto($_SERVER['HTTP_REFERER']);
      } else {
	drupal_goto('viewOutreach', array('query'=>array('OID'=>$newOID)));
      }
    }
  }
  drupal_set_message('There was an error...', 'error');
}

function backToEvent()
{
  $params = drupal_get_query_parameters(); //getting the parameters
  $OID = $params["OID"];

  drupal_goto("viewOutreach", array('query'=>array('OID'=>$OID)));
}

?>