<?php

function profileForm()  
{  
  global $user;
  $params = drupal_get_query_parameters();
  $new = true;

  if(isset($params["UID"])){
    $data = dbGetUserProfile($params["UID"]);
    $teams = dbGetTeamsForUser($params['UID']);
    $TID = $teams[0]['TID'];
    $emails = dbGetEmailsForUser($params["UID"]);
    $new = false;
  }

  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Enter user information below'),
    );


    $teams = dbSelectAllTeams();
    $names = array();
    foreach($teams as $team)
      {
        $names[$team["TID"]] = $team["name"];
      }

    $form['fields']['TID']=array(
 '#prefix'=>'<table><tr><td colspan="1" style="text-align:center">',
 '#type'=>'select',
 '#title'=>t('Team'),
 '#options'=>$names,
 '#default_value'=>$new?'':$TID,
 '#suffix'=>'</td>'
 );


    $form['fields']['addTeamText']=array(
  '#prefix'=>'<td colspan="5">',
  '#markup'=>"<b>Don't see your team?</b> Create it <a href=\"?q=teamForm\">here!</a> Once you create your team, you will be redirected back to this page.",
  '#suffix'=>'</td></tr>'
	);



    $form['fields']['firstName']=array(
        '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('First Name'),
	'#default_value'=>$new?'':$data['firstName'],
	'#suffix'=>'</td>');

    $form['fields']['lastName']=array(
	 '#prefix'=>'<td colspan="3" style="text-align:center">',
	 '#type'=>'textfield',
	 '#title'=>t('Last Name'),
	 '#default_value'=>$new?'':$data['lastName'],
	 '#suffix'=>'</td></tr>');

    $form['fields']['bio']=array(
        '#prefix'=>'<tr><td colspan="6">',
        '#type'=>'textarea',
        '#title'=>t('A Short Bio of Yourself:'),
	'#default_value'=>$new?'':$data['bio'],
	'#suffix'=>'</td></tr>');

    $form['fields']['position']=array(
        '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
	'#type'=>'textfield',
        '#title'=>t('Position on The Team'),
	'#default_value'=>$new?'':$data['position'],	
	'#suffix'=>'</td>');

    $form['fields']['phone']=array(
      '#prefix'=>'<td colspan="2" style="text-align:center">',
      '#type'=>'textfield',
      '#title'=>t('Your Phone Number (Numbers Only)'),
      '#default_value'=>$new?'':$data['phone'],
      '#suffix'=>'</td>');

    $form['fields']['primaryemail']=array(
     '#prefix'=>'<td colspan="2" style="text-align:center">',
     '#markup'=>"Primary Email <br>" . $user->mail,
     '#suffix'=>'</td></tr>');

   $form['fields']['extraEmail']=array(
     '#prefix'=>'<td colspan="2" style="text-align:center">',
     '#type'=>'textfield',
     '#title'=>t('Enter Your Secondary  Email'),
     '#default_value'=>$new?'':$emails,
     '#suffix'=>'</td></tr>');


    $form['fields']['grade']=array(
       '#prefix'=>'<tr><td colspan="1" style="text-align:center">',
       '#type'=>'select',
       '#options'=>array('1'=>'1st','2'=>'2nd', '3'=>'3rd','4'=>'4th','5'=>'5th','6'=>'6th','7'=>'7th','8'=>'8th','9'=>'9th','10'=>'10th','11'=>'11th','12'=>'12th'),       
       '#title'=>t('Grade'),
       '#default_value'=>$new?'':$data['grade'],       
       '#suffix'=>'</td>');

    $form['fields']['gender']=array(
        '#prefix'=>'<td colspan="1" style="text-align:center">',
        '#type'=>'radios',
	'#options'=> array('M'=>'Male', 'F'=>'Female', 'O'=>'Other'),
        '#title'=>t('Gender'),
	'#default_value'=>$new?'':$data['gender'],	
	'#suffix'=>'</td>');

    $form['fields']['picture']=array(
         '#markup'=>'<td colspan="4" style="text-align:center">'
				     );

    $form['fields']['FID']=array(
         '#title'=>t('Picture'),
         '#type'=>'managed_file',
         '#upload_location' => 'public://',
         '#upload_validators' => array(
	    'file_validate_extensions' => array('gif png jpg jpeg'),
            'file_validate_size' => array(50000*1024),         // 500k limit currently
	 ),
         '#default_value'=>$new?'':$data['FID'],	
         '#size' => 48,
     );

   $form['footer']=array('#markup'=>'</td></tr></table>');

   $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit'));

    
    return $form;

}


function profileForm_validate($form, $form_state)
{
  if(empty($form_state['values']['firstName']))
     form_set_error('firstName','First name cannot be empty');

  if(empty($form_state['values']['lastName']))
    form_set_error('lastName','Last name cannot be empty');
  
  if(!empty($form_state['values']['phone'])) {
    if(!is_numeric($form_state['values']['phone'])) {
	form_set_error('phone','That is not a valid phone number!');
    }
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

  $fields = array("firstName", "lastName", "bio", "position", "phone", "grade", "gender", "FID");
  $profileData = getFields($fields, $form_state['values']);
  $profileData['UID'] = $UID;

  if ($profileData['FID'] != null){
    $f = file_load($profileData['FID']);
    $f->status = FILE_STATUS_PERMANENT;
    file_save($f);
    file_usage_add($f, 'CROMA - users', 'pictures', $f->fid); // tells Drupal we're using the file
  }

  $emails = array();
  $emails['email'] = $form_state['values']['extraEmail'];
  $emails['UID'] = $profileData['UID'];
  
  if(dbGetUserProfile($profileData['UID']) == false)
    {
      $UID = dbCreateProfile($profileData);
      if ($UID != false){
	dbAddEmailsToUser($UID, $emails);
	dbAssignUserToTeam($UID, $form_state['values']['TID']);
      }
      drupal_set_message("Your profile has been created!");
    }
  else
    {
      dbUpdate("profiles",$profileData,"UID", $profileData['UID']);
      // dbUpdate("emailsVsUsers", $form_state['value']['extraEmail'], "UID", $profileData['UID']);
      if ($form_state['values']['TID'] != $form['fields']['TID']['#default_value']){ // if the team changed
	dbAssignUserToTeam($profileData['UID'], $form_state['values']['TID']);
      }
      drupal_set_message("Your profile has been updated!");
    }

  drupal_goto("viewUser", array('query'=>array('UID'=>$UID)));
}

?>