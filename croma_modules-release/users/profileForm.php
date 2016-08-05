<?php
/*
  Used to allow users to add/edit their profile

  - Contents -
  profileForm() - paints form to allow user to edit profile (including secondary email)
*/   

function profileForm($form, &$form_state)  
{  
  global $user;
  $params = drupal_get_query_parameters();
  $new = true;

  if(isset($params["UID"])){ // editing a user other than the current one
    $UID = $params['UID'];
  } else { // user is editing him/her own profile
    $UID = $user->uid;
  }
  
  $data = dbGetUserProfile($UID);
  if (empty($data)){ // if the UID passed did not have any user data associated with it
  } else {
    $new = false; // editing a user which already exists
  }

  // beginning the form
  $form = array();
  
  $form['fields']=array(
			'#type'=>'fieldset',
			'#title'=>t('Edit: User Info'),
			);


  $form['fields']['tableHeader']=array(
				       '#markup'=>'<table>'
				       );

  // checking permissions
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

  if($user->uid==$UID){
    $canEdit = true;
  }

  if(!$canEdit){
    drupal_set_message("You don't have permission to edit this user!", 'error');
        return;
  }
  

  if(!$new){ // if the profile is not new
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToProfile'),
				  '#type' => 'submit',				
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }


  $form['fields']['firstName']=array( // user's first name
				     '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				     '#type'=>'textfield',
				     '#title'=>t('First Name'),
				     '#default_value'=>$new?'':$data['firstName'],
				     '#suffix'=>'</td>');

  $form['fields']['lastName']=array( // user's last name
				    '#prefix'=>'<td colspan="3" style="text-align:center">',
				    '#type'=>'textfield',
				    '#title'=>t('Last Name'),
				    '#default_value'=>$new?'':$data['lastName'],
				    '#suffix'=>'</td></tr>');

  $form['fields']['primaryEmail']=array( // user's email
					'#prefix'=>'<td colspan="3" style="text-align:center">',
					'#markup'=>"Primary Email <br>" . $user->mail,
					'#suffix'=>'</td>');

  if(!$new){
    $secondaryEmail = dbGetSecondaryEmailForUser($UID);
  }

  $form['fields']['secondaryEmail']=array( // user's secondary email
					  '#prefix'=>'<td colspan="3" style="text-align:center">',
					  '#type'=>'textfield',
					  '#title'=>t('Secondary Email'),
					  '#default_value'=>$new?'':$secondaryEmail,
					  '#suffix'=>'</td></tr>');

  $form['fields']['gender']=array( // user's gender
				  '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
				  '#type'=>'radios',
				  '#options'=> array('Male'=>'Male', 'Female'=>'Female', 'Other'=>'Other'),
				  '#title'=>t('Gender'),
				  '#default_value'=>$new?'':$data['gender'],	
				  '#suffix'=>'</td>');

  $form['fields']['type']=array( // user's "type" --> i.e. student, mentor or alum
				  '#prefix'=>'<td colspan="2" style="text-align:center">',
				  '#type'=>'radios',
				  '#options'=> array('student'=>'Student', 'mentor'=>'Mentor', 'alumni'=>'Alumni'),
				  '#title'=>t('Type'),
				  '#default_value'=>$new?'':$data['type'],	
				  '#suffix'=>'</td>');

  $form['fields']['grade']=array( // user's grade (N/A is an option for mentors/alumni)
				 '#prefix'=>'<td colspan="2" style="text-align:center">',
				 '#type'=>'select',
				 '#options'=>array('1'=>'1st','2'=>'2nd', '3'=>'3rd','4'=>'4th','5'=>'5th','6'=>'6th','7'=>'7th','8'=>'8th','9'=>'9th','10'=>'10th','11'=>'11th','12'=>'12th', '0'=>'N/A'),       
				 '#title'=>t('Grade'),
				 '#default_value'=>$new?'':$data['grade'],       
				 '#chosen'=>true,
				 '#suffix'=>'</td></tr>');

  $form['fields']['phone']=array( // user's phone number
				 '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				 '#type'=>'textfield',
				 '#title'=>t('Phone Number'),
				 '#default_value'=>$new?'':$data['phone'],
				 '#placeholder'=>'Format: XXXXXXXXXX',
				 '#suffix'=>'</td>');

  $form['fields']['position']=array( // user's position on the team
				    '#prefix'=>'<td colspan="3" style="text-align:center">',
				    '#type'=>'textfield',
				    '#title'=>t('Team Position'),
				    '#default_value'=>$new?'':$data['position'],
				    '#placeholder'=>"i.e. Chairman's Presenter",
				    '#suffix'=>'</td></tr>');

  $form['fields']['bio']=array( // user's bio --> not required
			       '#prefix'=>'<tr><td colspan="6">',
			       '#type'=>'textarea',
			       '#title'=>t('Short Bio'),
			       '#default_value'=>$new?'':$data['bio'],
			       '#suffix'=>'</td></tr>');


  // end of inputting info into the form

  $form['fields']['tabling']=array('#markup'=>'</td></tr><tr>'); 


  $form['fields']['tabling2']=array('#markup'=>'<td colspan="3"></td>');

  $form['fields']['submit']=array( // submitting user info/changes
			'#prefix'=>'<td colspan="3" style="text-align:right">',
			'#type'=>'submit',
			'#value'=>t('Save'),
			'#suffix'=>'</td>'
			);


  $form['footer']=array('#markup'=>'</tr></table>');

  return $form;
}


function profileForm_validate($form, $form_state)
{
  global $user;

  if(empty($form_state['values']['firstName']))
    form_set_error('firstName','First name cannot be empty.'); // checks that first name is not empty

  if(empty($form_state['values']['lastName']))
    form_set_error('lastName','Last name cannot be empty.'); // checks that last name is not empty

  if(empty($form_state['values']['gender']))
    form_set_error('gender','Please specify a gender.'); // checks that gender field is not empty

  if(!empty($form_state['values']['phone']) && !is_numeric($form_state['values']['phone']))
    form_set_error('phone','Phone numbers must be numeric!'); // checks that phone number is all numbers

  if(!empty($form_state['values']['secondaryEmail'])){ // doing checks regarding the user's secondary email
    if(ctype_space($form_state['values']['secondaryEmail'])){
      form_set_error('secondaryEmail', 'Please enter a valid email or leave the field blank.');
    } else if(dbCheckSecondaryEmailForUser($form_state['values']['secondaryEmail'], $user->uid) == false){
      form_set_error('secondaryEmail', 'This email has already been taken. Please email info@CROMA.ChapResearch.com for assistance.');
    }
  }

  // checks that bio isn't too long
  if (strlen($form_state['values']['bio']) > MAX_BIO_CHARS){
    form_set_error('bio','Your bio cannot be longer than '.MAX_BIO_CHARS.' characters!'); 
  }
}

function profileForm_submit($form, $form_state)
{
  global $user; 
  
  $params = drupal_get_query_parameters();

  if (!isset($params['UID'])){
    $UID = $user->uid;
  } else { 
    $UID = $params['UID'];
  }

  // getting the inputted info from the fields
  $fields = array("firstName", "lastName", "position", "phone", "grade", "gender", "FID", "type");
  $profileData = getFields($fields, $form_state['values']);
  $profileData = stripTags($profileData, '');
  $profileData['UID'] = $UID;
  $profileData['bio'] = stripTags(array($form_state['values']['bio'])); // allow some tags in the bio only


  if(dbUserHasProfile($profileData['UID']) == false){ // if the user doesn't have a profile
    $result = dbCreateProfile($profileData); // creating new profile
    if ($result != false){
      drupal_set_message("Your profile has been created!"); // if it went through successfully
    } else {
      drupal_set_message("There was an error."); // if something "bad" occured during submission
    }
  } else { // if the user is simply editing existing profile
    dbUpdate("profiles",$profileData,"UID", $profileData['UID']);
    drupal_set_message("Profile has been updated!");
  }

  if(!empty($form_state['values']['secondaryEmail'])){ // user entered value
    if(dbGetSecondaryEmailForUser($profileData['UID']) == false){ // the user is adding a new secondary email
      dbAddEmailsToUser($profileData['UID'], array(trim($form_state['values']['secondaryEmail'])));
    } else { //  user is updating an old email
      dbUpdate('emailsVsUsers', array('email'=>$form_state['values']['secondaryEmail']), "UID", $profileData['UID']);
    } 
  } else { // user didn't enter value
    dbRemoveEntry('emailsVsUsers', 'UID', $profileData['UID']);
  }
	

  drupal_goto("viewUser", array('query'=>array('UID'=>$UID)));
}

function backToProfile()
{
  global $user;
  $UID = $user->uid;
  $params = drupal_get_query_parameters();
  if(isset($params['UID'])){
  $UID = $params['UID'];
  }
  drupal_goto("viewUser", array('query'=>array('UID'=>$UID)));
}

?>